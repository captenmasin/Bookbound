export function useContrast (
    hex: string = '',
    tooBright: string = 'text-secondary',
    justRight: string = '',
    bound: number = 150
): string {
    // Handle CSS variables
    if (hex.includes('var')) {
        if (typeof document === 'undefined') {
            return justRight
        }

        const hexString = hex.replaceAll('var(', '').replaceAll(')', '')
        hex = getComputedStyle(document.documentElement)
            .getPropertyValue(hexString)
            .trim()
    }

    // Remove '#' from the hex code
    let hexColor = hex.replace('#', '')

    // Expand shorthand hex code (e.g. "FFF" to "FFFFFF")
    if (hexColor.length === 3) {
        hexColor = hexColor
            .split('')
            .map((char) => char + char)
            .join('')
    }

    // Extract RGB values
    const r = parseInt(hexColor.substr(0, 2), 16)
    const g = parseInt(hexColor.substr(2, 2), 16)
    const b = parseInt(hexColor.substr(4, 2), 16)

    // Calculate brightness using YIQ formula
    const yiq = (r * 299 + g * 587 + b * 114) / 1000

    // Return appropriate class based on contrast
    return yiq >= bound ? tooBright : justRight
}
