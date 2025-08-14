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
const result = ref(null)
const book = ref(null)
const loading = ref(false)

let controls = null

const { play } = useSound(BarcodeScanned)
const { vibrate } = useVibrate({ pattern: [300, 100] })

// single shared reader instance
const codeReader = new BrowserMultiFormatReader()

// start scanning ------------------------------------------------------------
async function startScan () {
    play()

    // reset UI
    result.value = null
    book.value = null
    scanning.value = true

    useRequest(useRoute('api.books.fetch_or_create', '9780307763051'), 'GET')
        .then(response => {
            book.value = response.book
            result.value = '9780307763051'
        })

    try {
        controls = await codeReader.decodeFromConstraints(
            {
                video: { facingMode: { ideal: 'environment' } }
            },
            video.value,
            async (output) => {
                if (!output) return

                // we have a barcode!
                const identifier = output.getText()
                result.value = identifier
                stopScan()

                // hit your API
                loading.value = true
                useRequest(useRoute('api.books.fetch_or_create', identifier), 'GET')
                    .then(response => {
                        if (response.book) {
                            book.value = response.book
                            loading.value = false
                        } else {
                            console.error('No book found for identifier:', identifier)
                            result.value = null
                            book.value = null
                            loading.value = false
                        }
                    }).catch(error => {
                        console.error('Error fetching book:', error)
                        toast.error('Error fetching book details')
                        loading.value = false
                        result.value = null
                        book.value = null
                    })

                play()
                vibrate()

                // stopScan()
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
        controls.stop() // stop the camera
    }
}

const emit = defineEmits(['close'])

function fake () {
    useRequest(useRoute('api.books.fetch_or_create', '9780307763051'), 'GET')
        .then(response => {
            book.value = response.book
            result.value = '9780307763051'
        })
}

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
            v-show="scanning && !result">
            <div class="relative h-56 overflow-hidden shadow rounded">
                <video
                    ref="video"
                    class="mx-auto bg-muted absolute top-1/2 -translate-y-1/2 left-0 size-full object-cover"
                    autoplay
                    playsinline
                    muted
                />
                <div class="absolute top-1/2 left-0 w-full bg-red-500 opacity-75 shadow-xl shadow-red-500 h-[2px] animate-scan" />
            </div>
        </div>

        <div
            v-if="!scanning && !result"
            class="flex mb-5 items-center justify-center">
            <Button
                class="w-full"
                variant="default"
                @click="startScan">
                <Icon
                    name="ScanBarcode"
                    class="w-4" />
                Start scanning
            </Button>
        </div>

        <div
            v-if="result"
            class="relative h-40 overflow-hidden bg-white shadow rounded">
            <VueBarcode
                tag="svg"
                :value="result"
                class="w-full absolute top-1/2 -translate-y-1/2 left-0" />
        </div>

        <HorizontalSkeleton
            v-if="loading"
            class="mt-4"
            :with-actions="false"
        />

        <div
            v-if="book && !loading"
            class="mt-4 p-1">
            <BookCardHorizontal
                target="_blank"
                :book="book"
                :narrow="true" />
        </div>

        <!--        <button @click="fake">-->
        <!--            Fake-->
        <!--        </button>-->

        <div class="w-full mt-8">
            <Button
                v-if="result"
                variant="default"
                class="w-full"
                @click="startScan">
                <Icon
                    name="ScanBarcode"
                    class="w-4" />
                Scan again
            </Button>
        </div>
    </div>
</template>
