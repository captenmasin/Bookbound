<script setup>
import Icon from '@/components/Icon.vue'
import VueBarcode from '@chenfengyuan/vue-barcode'
import BarcodeScanned from '~/audio/barcode-scanned.mp3'
import HorizontalSkeleton from '@/components/books/HorizontalSkeleton.vue'
import BookCardHorizontal from '@/components/books/BookCardHorizontal.vue'
import { toast } from 'vue-sonner'
import { useSound } from '@vueuse/sound'
import { useVibrate } from '@vueuse/core'
import { useRoute } from '@/composables/useRoute'
import { useRequest } from '@/composables/useRequest'
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { Button } from '@/components/ui/button/index.js'
import { BrowserMultiFormatReader } from '@zxing/browser'

// refs for UI state
const video = ref(null)
const scanning = ref(false)
const currentResult = ref(null)
const scannedBooks = ref([])
const loadingIdentifier = ref(null)
const processingIdentifier = ref(null)

let controls = null

const { play } = useSound(BarcodeScanned)
const { vibrate } = useVibrate({ pattern: [300, 100] })

// single shared reader instance
const codeReader = new BrowserMultiFormatReader()

// start scanning ------------------------------------------------------------
async function startScan () {
    if (scanning.value) {
        return
    }

    play()
    scanning.value = true

    try {
        controls = await codeReader.decodeFromConstraints(
            {
                video: { facingMode: { ideal: 'environment' } }
            },
            video.value,
            async (output) => {
                if (!output || loadingIdentifier.value || processingIdentifier.value) {
                    return
                }

                const identifier = output.getText()

                if (!identifier || hasScannedBook(identifier)) {
                    processingIdentifier.value = null
                    return
                }

                processingIdentifier.value = identifier
                loadingIdentifier.value = identifier

                useRequest(useRoute('api.books.fetch_or_create', identifier), 'GET')
                    .then(response => {
                        if (response.book) {
                            currentResult.value = identifier
                            scannedBooks.value.unshift(response.book)
                            play()
                            vibrate()
                        } else {
                            console.error('No book found for identifier:', identifier)
                        }
                    }).catch(error => {
                        console.error('Error fetching book:', error)
                        toast.error('Error fetching book details')
                    }).finally(() => {
                        loadingIdentifier.value = null
                        processingIdentifier.value = null
                    })
            }
        )
    } catch (err) {
        console.error('Barcode scanning error:', err)
        scanning.value = false
    }
}

// stop scanning -------------------------------------------------------------
function stopScan () {
    scanning.value = false
    if (controls) {
        controls.stop()
        controls = null
    }
}

function hasScannedBook (identifier) {
    return scannedBooks.value.some(book => book.identifier === identifier)
}

// const emit = defineEmits(['close'])

// function fake () {
//     useRequest(useRoute('api.books.fetch_or_create', '9780307763051'), 'GET')
//         .then(response => {
//             book.value = response.book
//             result.value = '9780307763051'
//         })
// }

// cleanup if user navigates away --------------------------------------------
onBeforeUnmount(stopScan)

onMounted(() => {
    startScan()
})
</script>

<template>
    <div class="relative">
        <!-- mirrored only on front cam -->
        <div
            v-show="scanning">
            <div class="relative h-56 overflow-hidden rounded shadow">
                <video
                    ref="video"
                    class="absolute top-1/2 left-0 mx-auto -translate-y-1/2 object-cover bg-muted size-full"
                    autoplay
                    playsinline
                    muted
                />
                <div class="absolute top-1/2 left-0 w-full bg-red-500 opacity-75 shadow-xl shadow-red-500 h-[2px] animate-scan" />
            </div>
        </div>

        <div
            v-if="currentResult"
            class="mt-4 overflow-hidden rounded bg-white shadow">
            <div class="border-b px-4 py-2 text-xs uppercase tracking-[0.2em] text-muted-foreground">
                Last scanned
            </div>
            <VueBarcode
                tag="svg"
                :value="currentResult"
                class="w-full bg-white px-4 py-6" />
        </div>

        <HorizontalSkeleton
            v-if="loadingIdentifier"
            class="mt-4"
            :with-actions="false"
        />

        <div
            v-if="scannedBooks.length > 0"
            class="mt-4 space-y-4 p-1">
            <BookCardHorizontal
                v-for="scannedBook in scannedBooks"
                :key="scannedBook.id"
                target="_blank"
                :book="scannedBook"
                :narrow="true" />
        </div>

        <!--        <button @click="fake">-->
        <!--            Fake-->
        <!--        </button>-->

        <div class="mt-8 w-full">
            <Button
                :variant="scanning ? 'secondary' : 'default'"
                class="w-full"
                @click="scanning ? stopScan() : startScan()">
                <Icon
                    name="ScanBarcode"
                    class="w-4" />
                {{ scanning ? 'Stop scanning' : 'Start scanning' }}
            </Button>
        </div>
    </div>
</template>
