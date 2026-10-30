import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::store
* @see app/Http/Controllers/Portal/Company/PremisesController.php:93
* @route '/portal/premises/{premises}/checklist/{item}/photos'
*/
export const store = (args: { premises: number | { id: number }, item: number | { id: number } } | [premises: number | { id: number }, item: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/portal/premises/{premises}/checklist/{item}/photos',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::store
* @see app/Http/Controllers/Portal/Company/PremisesController.php:93
* @route '/portal/premises/{premises}/checklist/{item}/photos'
*/
store.url = (args: { premises: number | { id: number }, item: number | { id: number } } | [premises: number | { id: number }, item: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            premises: args[0],
            item: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        premises: typeof args.premises === 'object'
        ? args.premises.id
        : args.premises,
        item: typeof args.item === 'object'
        ? args.item.id
        : args.item,
    }

    return store.definition.url
            .replace('{premises}', parsedArgs.premises.toString())
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::store
* @see app/Http/Controllers/Portal/Company/PremisesController.php:93
* @route '/portal/premises/{premises}/checklist/{item}/photos'
*/
store.post = (args: { premises: number | { id: number }, item: number | { id: number } } | [premises: number | { id: number }, item: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::store
* @see app/Http/Controllers/Portal/Company/PremisesController.php:93
* @route '/portal/premises/{premises}/checklist/{item}/photos'
*/
const storeForm = (args: { premises: number | { id: number }, item: number | { id: number } } | [premises: number | { id: number }, item: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::store
* @see app/Http/Controllers/Portal/Company/PremisesController.php:93
* @route '/portal/premises/{premises}/checklist/{item}/photos'
*/
storeForm.post = (args: { premises: number | { id: number }, item: number | { id: number } } | [premises: number | { id: number }, item: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::destroy
* @see app/Http/Controllers/Portal/Company/PremisesController.php:110
* @route '/portal/premises/{premises}/photos/{photo}'
*/
export const destroy = (args: { premises: number | { id: number }, photo: number | { id: number } } | [premises: number | { id: number }, photo: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/portal/premises/{premises}/photos/{photo}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::destroy
* @see app/Http/Controllers/Portal/Company/PremisesController.php:110
* @route '/portal/premises/{premises}/photos/{photo}'
*/
destroy.url = (args: { premises: number | { id: number }, photo: number | { id: number } } | [premises: number | { id: number }, photo: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            premises: args[0],
            photo: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        premises: typeof args.premises === 'object'
        ? args.premises.id
        : args.premises,
        photo: typeof args.photo === 'object'
        ? args.photo.id
        : args.photo,
    }

    return destroy.definition.url
            .replace('{premises}', parsedArgs.premises.toString())
            .replace('{photo}', parsedArgs.photo.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::destroy
* @see app/Http/Controllers/Portal/Company/PremisesController.php:110
* @route '/portal/premises/{premises}/photos/{photo}'
*/
destroy.delete = (args: { premises: number | { id: number }, photo: number | { id: number } } | [premises: number | { id: number }, photo: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::destroy
* @see app/Http/Controllers/Portal/Company/PremisesController.php:110
* @route '/portal/premises/{premises}/photos/{photo}'
*/
const destroyForm = (args: { premises: number | { id: number }, photo: number | { id: number } } | [premises: number | { id: number }, photo: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::destroy
* @see app/Http/Controllers/Portal/Company/PremisesController.php:110
* @route '/portal/premises/{premises}/photos/{photo}'
*/
destroyForm.delete = (args: { premises: number | { id: number }, photo: number | { id: number } } | [premises: number | { id: number }, photo: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const photos = {
    store: Object.assign(store, store),
    destroy: Object.assign(destroy, destroy),
}

export default photos