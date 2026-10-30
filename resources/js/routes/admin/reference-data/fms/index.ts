import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::index
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:31
* @route '/admin/reference-data/fms'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/reference-data/fms',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::index
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:31
* @route '/admin/reference-data/fms'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::index
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:31
* @route '/admin/reference-data/fms'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::index
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:31
* @route '/admin/reference-data/fms'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::index
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:31
* @route '/admin/reference-data/fms'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::index
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:31
* @route '/admin/reference-data/fms'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::index
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:31
* @route '/admin/reference-data/fms'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::store
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:43
* @route '/admin/reference-data/fms'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/reference-data/fms',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::store
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:43
* @route '/admin/reference-data/fms'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::store
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:43
* @route '/admin/reference-data/fms'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::store
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:43
* @route '/admin/reference-data/fms'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::store
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:43
* @route '/admin/reference-data/fms'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::update
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:50
* @route '/admin/reference-data/fms/{fms}'
*/
export const update = (args: { fms: number | { id: number } } | [fms: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/admin/reference-data/fms/{fms}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::update
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:50
* @route '/admin/reference-data/fms/{fms}'
*/
update.url = (args: { fms: number | { id: number } } | [fms: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fms: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { fms: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            fms: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fms: typeof args.fms === 'object'
        ? args.fms.id
        : args.fms,
    }

    return update.definition.url
            .replace('{fms}', parsedArgs.fms.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::update
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:50
* @route '/admin/reference-data/fms/{fms}'
*/
update.put = (args: { fms: number | { id: number } } | [fms: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::update
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:50
* @route '/admin/reference-data/fms/{fms}'
*/
const updateForm = (args: { fms: number | { id: number } } | [fms: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\ReferenceData\FederalMemberStateController::update
* @see app/Http/Controllers/Admin/ReferenceData/FederalMemberStateController.php:50
* @route '/admin/reference-data/fms/{fms}'
*/
updateForm.put = (args: { fms: number | { id: number } } | [fms: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const fms = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
    update: Object.assign(update, update),
}

export default fms