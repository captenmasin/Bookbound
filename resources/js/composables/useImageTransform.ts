import { useRoute } from '@/composables/useRoute'
import { ImageProps } from '@/types/image'
import { usePage } from '@inertiajs/vue3'
import { useDevicePixelRatio } from '@vueuse/core'
import { computed, onMounted, ref } from 'vue'

interface ImageTransformOptions {
    width?: number | string | null;
    height?: number | string | null;
    format?: string;
    quality?: number | string | null;
    flip?: 'h' | 'v' | 'hv';
    contrast?: number | string | null;
    version?: number | string | null;
    background?: string;
    blur?: number | string | null;
    scale?: boolean | string | null;
    crop?: 'top' | 'bottom' | 'left' | 'right' | 'center';
    pixelate?: number | string | null;
    rotate?: number | string | null;
}

export function useImageTransform () {
    const { props } = usePage()
    const { pixelRatio } = useDevicePixelRatio()
    const hasMounted = ref(false)
    const effectivePixelRatio = computed(() => (hasMounted.value ? pixelRatio.value : 1))

    onMounted(() => {
        hasMounted.value = true
    })

    const cleanPath = (rawPath: string): string => {
        /* eslint-disable-next-line no-useless-escape */
        const storageUrl = (props as any).app.storage_url || ''
        let path = rawPath.replace(storageUrl, '')

        if (path.startsWith('/storage/')) path = path.slice(9)
        else if (path.startsWith('storage/')) path = path.slice(8)

        return path.startsWith('/') ? path.slice(1) : path
    }

    // eslint-disable-next-line sonarjs/cognitive-complexity
    const baseOptions = (props: ImageProps): Record<string, string | number> | null => {
        const result: Record<string, string | number> = {}

        for (const key of ['width', 'height', 'format', 'quality', 'flip', 'contrast', 'version', 'background', 'blur', 'scale', 'crop']) {
            const val = props[key as keyof typeof props]

            if (val === null || val === undefined || val === '' || val === 0 || val === 'auto') {
                continue
            }

            if (key === 'crop' && (!props.width || !props.height)) {
                continue
            }

            let processed: string | number | number[]

            if (Array.isArray(val)) {
                processed = val.join(',')
            } else if (typeof val === 'boolean') {
                processed = val ? 'true' : 'false'
            } else if (typeof val === 'string') {
                processed = val.toLowerCase()
            } else {
                processed = val
            }

            result[key] = processed
        }

        return Object.keys(result).length > 0 ? result : null
    }

    const buildOptionsString = (options: ImageTransformOptions): string => {
        return Object.entries(options)
            .filter(([key, value]) => {
                if (key === 'crop') {
                    return options.width && options.height && value !== null && value !== undefined
                }

                return value !== null && value !== undefined && value !== '' && value !== 0 && value !== 'auto'
            })
            .map(([key, value]) => {
                if (key === 'width' || key === 'height') {
                    if (typeof value !== 'number') {
                        value = parseInt(value as string, 10)
                    }

                    value = Math.round(value * effectivePixelRatio.value)
                }

                if (typeof value === 'boolean') return `${key}=${value ? 'true' : 'false'}`

                return `${key}=${String(value).toLowerCase()}`
            })
            .join(',')
    }

    const getImageUrl = (path: string, options: ImageTransformOptions = {}) => {
        const cleaned = cleanPath(path)
        const optString = buildOptionsString(options)

        if (cleaned.startsWith('http://') || cleaned.startsWith('https://')) {
            return path
        }

        if (!optString) {
            return `/storage/${cleaned}`
        }

        return useRoute('image.transform', {
            path: cleaned,
            options: optString
        })
    }

    const buildSrcSet = (props: ImageProps) => {
        if (!props.enableSrcset || props.height) return null

        let sizes = [...(props.srcsetSizes ? props.srcsetSizes : []), props.srcsetMaxWidth as number]

        if (props.width) {
            const maxWidth = parseInt(props.width as string, 10)
            if (maxWidth > 0) {
                sizes.push(maxWidth)
                sizes = sizes.filter((size) => size <= maxWidth)
            }
        }

        return sizes
            .map((width) => {
                const options = {
                    ...baseOptions(props),
                    width,
                    height: null
                }
                return `${getImageUrl(props.src, options)} ${width}w`
            })
            .filter(Boolean)
            .join(', ')
    }

    return {
        cleanPath,
        baseOptions,
        buildSrcSet,
        buildOptionsString,
        getImageUrl
    }
}
