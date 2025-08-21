// @ts-ignore
import { Ziggy } from '@/routes.js'
// @ts-ignore
import { useRoute as useZiggyRoute } from 'ziggy-js'
export type RouteName = keyof typeof Ziggy.routes;

export function useRoute (name: RouteName | null | string = null, params: Object = {}, absolute: boolean = false): string {
    // @ts-ignore
    const route = useZiggyRoute(Ziggy)

    // @ts-ignore
    return route(name, params, absolute).toString()
}
