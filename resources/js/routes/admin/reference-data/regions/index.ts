import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\ReferenceData\RegionController::store
* @see app/Http/Controllers/Admin/ReferenceData/RegionController.php:27
* @route '/admin/reference-data/regions'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/reference-data/regions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ReferenceData\RegionController::store
* @see app/Http/Controllers/Admin/ReferenceData/RegionController.php:27
* @route '/admin/reference-data/regions'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ReferenceData\RegionController::store
* @see app/Http/Controllers/Admin/ReferenceData/RegionController.php:27
* @route '/admin/reference-data/regions'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\RegionController::store
* @see app/Http/Controllers/Admin/ReferenceData/RegionController.php:27
* @route '/admin/reference-data/regions'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\RegionController::store
* @see app/Http/Controllers/Admin/ReferenceData/RegionController.php:27
* @route '/admin/reference-data/regions'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Admin\ReferenceData\RegionController::update
* @see app/Http/Controllers/Admin/ReferenceData/RegionController.php:34
* @route '/admin/reference-data/regions/{region}'
*/
export const update = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/admin/reference-data/regions/{region}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\ReferenceData\RegionController::update
* @see app/Http/Controllers/Admin/ReferenceData/RegionController.php:34
* @route '/admin/reference-data/regions/{region}'
*/
update.url = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { region: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { region: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            region: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        region: typeof args.region === 'object'
        ? args.region.id
        : args.region,
    }

    return update.definition.url
            .replace('{region}', parsedArgs.region.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ReferenceData\RegionController::update
* @see app/Http/Controllers/Admin/ReferenceData/RegionController.php:34
* @route '/admin/reference-data/regions/{region}'
*/
update.put = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\RegionController::update
* @see app/Http/Controllers/Admin/ReferenceData/RegionController.php:34
* @route '/admin/reference-data/regions/{region}'
*/
const updateForm = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\RegionController::update
* @see app/Http/Controllers/Admin/ReferenceData/RegionController.php:34
* @route '/admin/reference-data/regions/{region}'
*/
updateForm.put = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const regions = {
    store: Object.assign(store, store),
    update: Object.assign(update, update),
}

export default regions