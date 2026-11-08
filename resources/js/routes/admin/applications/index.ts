import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\ApplicationController::show
 * @see app/Http/Controllers/Admin/ApplicationController.php:53
 * @route '/admin/applications/{application}'
 */
export const show = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/applications/{application}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ApplicationController::show
 * @see app/Http/Controllers/Admin/ApplicationController.php:53
 * @route '/admin/applications/{application}'
 */
show.url = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { application: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { application: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    application: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        application: typeof args.application === 'object'
                ? args.application.id
                : args.application,
                }

    return show.definition.url
            .replace('{application}', parsedArgs.application.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ApplicationController::show
 * @see app/Http/Controllers/Admin/ApplicationController.php:53
 * @route '/admin/applications/{application}'
 */
show.get = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ApplicationController::show
 * @see app/Http/Controllers/Admin/ApplicationController.php:53
 * @route '/admin/applications/{application}'
 */
show.head = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\ApplicationController::show
 * @see app/Http/Controllers/Admin/ApplicationController.php:53
 * @route '/admin/applications/{application}'
 */
    const showForm = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\ApplicationController::show
 * @see app/Http/Controllers/Admin/ApplicationController.php:53
 * @route '/admin/applications/{application}'
 */
        showForm.get = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\ApplicationController::show
 * @see app/Http/Controllers/Admin/ApplicationController.php:53
 * @route '/admin/applications/{application}'
 */
        showForm.head = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Admin\ApplicationController::actions
 * @see app/Http/Controllers/Admin/ApplicationController.php:117
 * @route '/admin/applications/{application}/actions'
 */
export const actions = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: actions.url(args, options),
    method: 'post',
})

actions.definition = {
    methods: ["post"],
    url: '/admin/applications/{application}/actions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ApplicationController::actions
 * @see app/Http/Controllers/Admin/ApplicationController.php:117
 * @route '/admin/applications/{application}/actions'
 */
actions.url = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { application: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { application: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    application: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        application: typeof args.application === 'object'
                ? args.application.id
                : args.application,
                }

    return actions.definition.url
            .replace('{application}', parsedArgs.application.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ApplicationController::actions
 * @see app/Http/Controllers/Admin/ApplicationController.php:117
 * @route '/admin/applications/{application}/actions'
 */
actions.post = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: actions.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ApplicationController::actions
 * @see app/Http/Controllers/Admin/ApplicationController.php:117
 * @route '/admin/applications/{application}/actions'
 */
    const actionsForm = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: actions.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ApplicationController::actions
 * @see app/Http/Controllers/Admin/ApplicationController.php:117
 * @route '/admin/applications/{application}/actions'
 */
        actionsForm.post = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: actions.url(args, options),
            method: 'post',
        })
    
    actions.form = actionsForm
/**
* @see \App\Http\Controllers\Admin\ApplicationController::reviewItems
 * @see app/Http/Controllers/Admin/ApplicationController.php:149
 * @route '/admin/applications/{application}/review-items'
 */
export const reviewItems = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reviewItems.url(args, options),
    method: 'post',
})

reviewItems.definition = {
    methods: ["post"],
    url: '/admin/applications/{application}/review-items',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ApplicationController::reviewItems
 * @see app/Http/Controllers/Admin/ApplicationController.php:149
 * @route '/admin/applications/{application}/review-items'
 */
reviewItems.url = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { application: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { application: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    application: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        application: typeof args.application === 'object'
                ? args.application.id
                : args.application,
                }

    return reviewItems.definition.url
            .replace('{application}', parsedArgs.application.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ApplicationController::reviewItems
 * @see app/Http/Controllers/Admin/ApplicationController.php:149
 * @route '/admin/applications/{application}/review-items'
 */
reviewItems.post = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reviewItems.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ApplicationController::reviewItems
 * @see app/Http/Controllers/Admin/ApplicationController.php:149
 * @route '/admin/applications/{application}/review-items'
 */
    const reviewItemsForm = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reviewItems.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ApplicationController::reviewItems
 * @see app/Http/Controllers/Admin/ApplicationController.php:149
 * @route '/admin/applications/{application}/review-items'
 */
        reviewItemsForm.post = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reviewItems.url(args, options),
            method: 'post',
        })
    
    reviewItems.form = reviewItemsForm
/**
* @see \App\Http\Controllers\Admin\ApplicationController::file
 * @see app/Http/Controllers/Admin/ApplicationController.php:178
 * @route '/admin/applications/{application}/files/{storedFile}'
 */
export const file = (args: { application: number | { id: number }, storedFile: number | { id: number } } | [application: number | { id: number }, storedFile: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: file.url(args, options),
    method: 'get',
})

file.definition = {
    methods: ["get","head"],
    url: '/admin/applications/{application}/files/{storedFile}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ApplicationController::file
 * @see app/Http/Controllers/Admin/ApplicationController.php:178
 * @route '/admin/applications/{application}/files/{storedFile}'
 */
file.url = (args: { application: number | { id: number }, storedFile: number | { id: number } } | [application: number | { id: number }, storedFile: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    application: args[0],
                    storedFile: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        application: typeof args.application === 'object'
                ? args.application.id
                : args.application,
                                storedFile: typeof args.storedFile === 'object'
                ? args.storedFile.id
                : args.storedFile,
                }

    return file.definition.url
            .replace('{application}', parsedArgs.application.toString())
            .replace('{storedFile}', parsedArgs.storedFile.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ApplicationController::file
 * @see app/Http/Controllers/Admin/ApplicationController.php:178
 * @route '/admin/applications/{application}/files/{storedFile}'
 */
file.get = (args: { application: number | { id: number }, storedFile: number | { id: number } } | [application: number | { id: number }, storedFile: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: file.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ApplicationController::file
 * @see app/Http/Controllers/Admin/ApplicationController.php:178
 * @route '/admin/applications/{application}/files/{storedFile}'
 */
file.head = (args: { application: number | { id: number }, storedFile: number | { id: number } } | [application: number | { id: number }, storedFile: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: file.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\ApplicationController::file
 * @see app/Http/Controllers/Admin/ApplicationController.php:178
 * @route '/admin/applications/{application}/files/{storedFile}'
 */
    const fileForm = (args: { application: number | { id: number }, storedFile: number | { id: number } } | [application: number | { id: number }, storedFile: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: file.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\ApplicationController::file
 * @see app/Http/Controllers/Admin/ApplicationController.php:178
 * @route '/admin/applications/{application}/files/{storedFile}'
 */
        fileForm.get = (args: { application: number | { id: number }, storedFile: number | { id: number } } | [application: number | { id: number }, storedFile: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: file.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\ApplicationController::file
 * @see app/Http/Controllers/Admin/ApplicationController.php:178
 * @route '/admin/applications/{application}/files/{storedFile}'
 */
        fileForm.head = (args: { application: number | { id: number }, storedFile: number | { id: number } } | [application: number | { id: number }, storedFile: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: file.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    file.form = fileForm
const applications = {
    show: Object.assign(show, show),
actions: Object.assign(actions, actions),
reviewItems: Object.assign(reviewItems, reviewItems),
file: Object.assign(file, file),
}

export default applications