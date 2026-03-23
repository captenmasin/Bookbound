<script setup>
import Icon from '@/components/Icon.vue'
import VueBarcode from '@chenfengyuan/vue-barcode'
import BarcodeScanned from '~/audio/barcode-scanned.mp3'
import BookCardHorizontal from '@/components/books/BookCardHorizontal.vue'
import HorizontalSkeleton from '@/components/books/HorizontalSkeleton.vue'
import { toast } from 'vue-sonner'
import { useSound } from '@vueuse/sound'
import { useVibrate } from '@vueuse/core'
import { useRoute } from '@/composables/useRoute'
import { useRequest } from '@/composables/useRequest'
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { Button } from '@/components/ui/button/index.js'
import { useNativeApp } from '@/composables/useNativeApp'
import { BrowserMultiFormatReader } from '@zxing/browser'

// refs for UI state
const video = ref(null)
const scanning = ref(false)
const result = ref(null)
const book = ref(null)
const loading = ref(false)
const nativeScannerPermissionIssue = ref(false)

let controls = null

const { isNative, scanBarcode, vibrate: vibrateNative, openAppSettings, storeSecureValue } = useNativeApp()
const { play } = useSound(BarcodeScanned)
const { vibrate } = useVibrate({ pattern: [300, 100] })

// single shared reader instance
const codeReader = new BrowserMultiFormatReader()

async function fetchBookByIdentifier (identifier) {
    loading.value = true

    try {
        const response = await useRequest(useRoute('api.books.fetch_or_create', identifier), 'GET')

        if (response.book) {
            book.value = response.book
            await storeSecureValue('last_scanned_identifier', identifier)
        } else {
            console.error('No book found for identifier:', identifier)
            result.value = null
            book.value = null
        }
    } catch (error) {
        console.error('Error fetching book:', error)
        toast.error('Error fetching book details')
        result.value = null
        book.value = null
    } finally {
        loading.value = false
    }
}

// start scanning ------------------------------------------------------------
async function startScan () {
    play()

    // reset UI
    result.value = null
    book.value = null
    nativeScannerPermissionIssue.value = false
    scanning.value = true

    try {
        if (isNative) {
            const identifier = await scanBarcode()

            scanning.value = false

            if (!identifier) {
                return
            }

            result.value = identifier
            await fetchBookByIdentifier(identifier)
            play()
            await vibrateNative()

            return
        }

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
                await fetchBookByIdentifier(identifier)

                play()
                vibrate()
            }
        )
    } catch (err) {
        console.error('Barcode scanning error:', err)
        scanning.value = false

        if (isNative) {
            toast.error('Native scanner unavailable')
            nativeScannerPermissionIssue.value = true
        }
    }
}

// stop scanning -------------------------------------------------------------
function stopScan () {
    scanning.value = false
    if (controls) {
        controls.stop() // stop the camera
    }
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
        <div v-show="scanning && !result">
            <div class="relative h-56 overflow-hidden rounded shadow">
                <video
                    ref="video"
                    class="absolute top-1/2 left-0 mx-auto size-full -translate-y-1/2 bg-muted object-cover"
                    autoplay
                    playsinline
                    muted
                />
                <div class="absolute top-1/2 left-0 h-[2px] w-full animate-scan bg-red-500 opacity-75 shadow-xl shadow-red-500" />
            </div>
        </div>

        <div
            v-if="!scanning && !result"
            class="mb-5 flex items-center justify-center">
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
            v-if="nativeScannerPermissionIssue"
            class="mb-5 rounded-lg border border-destructive/30 bg-destructive/5 p-4 text-sm text-destructive">
            <p class="font-medium">
                Bookbound could not access the native scanner.
            </p>
            <p class="mt-1 text-destructive/80">
                Review Android camera and scanner permissions in app settings, then try again.
            </p>
            <Button
                variant="secondary"
                class="mt-4 w-full"
                @click="void openAppSettings()">
                Open app settings
            </Button>
        </div>

        <div
            v-if="result"
            class="relative h-40 overflow-hidden rounded bg-white shadow">
            <VueBarcode
                tag="svg"
                :value="result"
                class="absolute top-1/2 left-0 w-full -translate-y-1/2" />
        </div>

        <HorizontalSkeleton
            v-if="loading"
            class="mt-4"
            :with-actions="false" />

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

        <div class="mt-8 w-full">
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
