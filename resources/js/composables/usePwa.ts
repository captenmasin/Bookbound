type PwaState = {
    isPwa: boolean;
    isAndroid: boolean;
    isIos: boolean;
    isMacos: boolean;
}

export function usePwa (): PwaState {
    if (typeof document === 'undefined') {
        return {
            isPwa: false,
            isAndroid: false,
            isIos: false,
            isMacos: false,
        }
    }

    const cookies = document.cookie.split('; ')
    const pwaMode = cookies.find(row => row.startsWith('pwa-mode='))
    const pwaDevice = cookies.find(row => row.startsWith('pwa-device='))

    return {
        isPwa: pwaMode ? pwaMode.split('=')[1] === 'true' : false,
        isAndroid: pwaDevice ? pwaDevice.split('=')[1] === 'android' : false,
        isIos: pwaDevice ? pwaDevice.split('=')[1] === 'ios' : false,
        isMacos: pwaDevice ? pwaDevice.split('=')[1] === 'macos' : false,
    }
}
