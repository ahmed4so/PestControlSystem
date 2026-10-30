import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::store
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:31
* @route '/signup'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/signup',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::store
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:31
* @route '/signup'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::store
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:31
* @route '/signup'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::store
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:31
* @route '/signup'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::store
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:31
* @route '/signup'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const signup = {
    store: Object.assign(store, store),
}

export default signup