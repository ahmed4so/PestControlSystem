import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \Laravel\Jetstream\Http\Controllers\Inertia\ProfilePhotoController::destroy
 * @see vendor/laravel/jetstream/src/Http/Controllers/Inertia/ProfilePhotoController.php:16
 * @route '/user/profile-photo'
 */
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/user/profile-photo',
} satisfies RouteDefinition<["delete"]>

/**
* @see \Laravel\Jetstream\Http\Controllers\Inertia\ProfilePhotoController::destroy
 * @see vendor/laravel/jetstream/src/Http/Controllers/Inertia/ProfilePhotoController.php:16
 * @route '/user/profile-photo'
 */
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \Laravel\Jetstream\Http\Controllers\Inertia\ProfilePhotoController::destroy
 * @see vendor/laravel/jetstream/src/Http/Controllers/Inertia/ProfilePhotoController.php:16
 * @route '/user/profile-photo'
 */
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

    /**
* @see \Laravel\Jetstream\Http\Controllers\Inertia\ProfilePhotoController::destroy
 * @see vendor/laravel/jetstream/src/Http/Controllers/Inertia/ProfilePhotoController.php:16
 * @route '/user/profile-photo'
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
* @see \Laravel\Jetstream\Http\Controllers\Inertia\ProfilePhotoController::destroy
 * @see vendor/laravel/jetstream/src/Http/Controllers/Inertia/ProfilePhotoController.php:16
 * @route '/user/profile-photo'
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
const currentUserPhoto = {
    destroy: Object.assign(destroy, destroy),
}

export default currentUserPhoto