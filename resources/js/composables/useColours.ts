export function useColours () {
    function resolveCssVar (hex: string): string {
        if (typeof document === 'undefined') {
            return hex
        }

        const varName = hex.replace('var(', '').replace(')', '').trim()
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim()
    }

    function hexToRgb (hex: string): string {
        if (hex.includes('var(')) {
            hex = resolveCssVar(hex)
        }

        const hexcolor = hex.replace('#', '').trim()
        if (hexcolor.length !== 6) return '0, 0, 0' // fallback

        const r = parseInt(hexcolor.substring(0, 2), 16)
        const g = parseInt(hexcolor.substring(2, 4), 16)
        const b = parseInt(hexcolor.substring(4, 6), 16)

        return `${r}, ${g}, ${b}`
    }

    function changeColourOpacity (colour: string, opacity: number): string {
        if (colour.includes('var(')) {
            colour = resolveCssVar(colour)
        }

        if (colour.includes('#')) {
            colour = hexToRgb(colour)
        }

        let rgbParts = colour.replace('rgb(', '').replace(')', '').split(',').map(c => parseInt(c.trim()))
        if (rgbParts.length !== 3 || rgbParts.some(isNaN)) rgbParts = [0, 0, 0] // fallback

        const [r, g, b] = rgbParts
        return `rgba(${r}, ${g}, ${b}, ${opacity})`
    }

    return {
        hexToRgb,
        changeColourOpacity
    }
}
