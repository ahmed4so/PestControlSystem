import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::store
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:33
* @route '/temporary-password'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/temporary-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::store
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:33
* @route '/temporary-password'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::store
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:33
* @route '/temporary-password'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::store
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:33
* @route '/temporary-password'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::store
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:33
* @route '/temporary-password'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const temporaryPassword = {
    store: Object.assign(store, store),
}

export default temporaryPassword