import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\RoleController::store
* @see app/Http/Controllers/Admin/RoleController.php:127
* @route '/roles/assign'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/roles/assign',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\RoleController::store
* @see app/Http/Controllers/Admin/RoleController.php:127
* @route '/roles/assign'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\RoleController::store
* @see app/Http/Controllers/Admin/RoleController.php:127
* @route '/roles/assign'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\RoleController::store
* @see app/Http/Controllers/Admin/RoleController.php:127
* @route '/roles/assign'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\RoleController::store
* @see app/Http/Controllers/Admin/RoleController.php:127
* @route '/roles/assign'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const assign = {
    store: Object.assign(store, store),
}

export default assign