import type { AppPageProps } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { BridgeCall, Device, Dialog, Events, Off, On, Scanner, SecureStorage, Share } from '@nativephp/mobile'

type NativeScanResult = {
    data?: string;
    format?: string;
    id?: string | null;
};

export function useNativeApp () {
    const page = usePage<AppPageProps>()
    const app = page.props.app

    const isNative = app.is_native
    const platform = app.native_platform
    const capabilities = app.native_capabilities

    async function vibrate (): Promise<void> {
        if (!isNative || !capabilities.device) {
            return
        }

        await Device.vibrate()
    }

    async function shareUrl (title: string, text: string, url: string): Promise<boolean> {
        if (!isNative || !capabilities.share) {
            return false
        }

        await Share.url(title, text, url)

        return true
    }

    async function openAppSettings (): Promise<boolean> {
        if (!isNative || !capabilities.system) {
            return false
        }

        await BridgeCall('System.OpenAppSettings', {})

        return true
    }

    async function storeSecureValue (key: string, value: string): Promise<boolean> {
        if (!isNative || !capabilities.secure_storage) {
            return false
        }

        await SecureStorage.set(key, value)

        return true
    }

    async function getSecureValue (key: string): Promise<string | null> {
        if (!isNative || !capabilities.secure_storage) {
            return null
        }

        const result = await SecureStorage.get(key)

        return result.value
    }

    async function deleteSecureValue (key: string): Promise<boolean> {
        if (!isNative || !capabilities.secure_storage) {
            return false
        }

        await SecureStorage.delete(key)

        return true
    }

    async function scanBarcode (): Promise<string | null> {
        if (!isNative || !capabilities.scanner) {
            return null
        }

        const scanId = `bookbound-scan-${Date.now()}`

        return await new Promise((resolve, reject) => {
            const handleScanned = (payload: NativeScanResult) => {
                if (payload?.id && payload.id !== scanId) {
                    return
                }

                teardown()
                resolve(payload?.data ?? null)
            }

            const handleCancelled = () => {
                teardown()
                resolve(null)
            }

            const teardown = () => {
                Off(Events.Scanner.CodeScanned, handleScanned)
                Off(Events.Scanner.Cancelled, handleCancelled)
            }

            On(Events.Scanner.CodeScanned, handleScanned)
            On(Events.Scanner.Cancelled, handleCancelled)

            Scanner.scan()
                .id(scanId)
                .prompt('Scan a book barcode')
                .formats(['ean13', 'ean8', 'upca', 'upce'])
                .then(() => undefined)
                .catch(async (error: unknown) => {
                    teardown()

                    if (error instanceof Error && capabilities.system) {
                        await Dialog.alert(
                            'Scanner unavailable',
                            'Bookbound could not access the native scanner. You can review Android permissions in app settings.'
                        )
                    }

                    reject(error)
                })
        })
    }

    return {
        isNative,
        platform,
        capabilities,
        vibrate,
        shareUrl,
        openAppSettings,
        storeSecureValue,
        getSecureValue,
        deleteSecureValue,
        scanBarcode
    }
}
