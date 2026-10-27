import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \Laravel\Jetstream\Http\Controllers\Inertia\CurrentUserController::destroy
 * @see vendor/laravel/jetstream/src/Http/Controllers/Inertia/CurrentUserController.php:22
 * @route '/user'
 */
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/user',
} satisfies RouteDefinition<["delete"]>

/**
* @see \Laravel\Jetstream\Http\Controllers\Inertia\CurrentUserController::destroy
 * @see vendor/laravel/jetstream/src/Http/Controllers/Inertia/CurrentUserController.php:22
 * @route '/user'
 */
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \Laravel\Jetstream\Http\Controllers\Inertia\CurrentUserController::destroy
 * @see vendor/laravel/jetstream/src/Http/Controllers/Inertia/CurrentUserController.php:22
 * @route '/user'
 */
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

    /**
* @see \Laravel\Jetstream\Http\Controllers\Inertia\CurrentUserController::destroy
 * @see vendor/laravel/jetstream/src/Http/Controllers/Inertia/CurrentUserController.php:22
 * @route '/user'
 */
    const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \Laravel\Jetstream\Http\Controllers\Inertia\CurrentUserController::destroy
 * @see vendor/laravel/jetstream/src/Http/Controllers/Inertia/CurrentUserController.php:22
 * @route '/user'
 */
        destroyForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const currentUser = {
    destroy: Object.assign(destroy, destroy),
}

export default currentUser