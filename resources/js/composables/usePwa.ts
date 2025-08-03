export function usePwa () {
    const pwaMode = document.cookie.split('; ').find(row => row.startsWith('pwa-mode='))
    const pwaDevice = document.cookie.split('; ').find(row => row.startsWith('pwa-device='))

    const isPwa = pwaMode ? pwaMode.split('=')[1] === 'true' : false
    const isAndroid = pwaDevice ? pwaDevice.split('=')[1] === 'android' : false
    const isIos = pwaDevice ? pwaDevice.split('=')[1] === 'ios' : false
    const isMacos = pwaDevice ? pwaDevice.split('=')[1] === 'macos' : false

    return { isPwa, isAndroid, isIos, isMacos }
}
