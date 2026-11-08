import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Portal\ApplicationController::index
 * @see app/Http/Controllers/Portal/ApplicationController.php:39
 * @route '/portal/applications'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/portal/applications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\ApplicationController::index
 * @see app/Http/Controllers/Portal/ApplicationController.php:39
 * @route '/portal/applications'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\ApplicationController::index
 * @see app/Http/Controllers/Portal/ApplicationController.php:39
 * @route '/portal/applications'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Portal\ApplicationController::index
 * @see app/Http/Controllers/Portal/ApplicationController.php:39
 * @route '/portal/applications'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Portal\ApplicationController::index
 * @see app/Http/Controllers/Portal/ApplicationController.php:39
 * @route '/portal/applications'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Portal\ApplicationController::index
 * @see app/Http/Controllers/Portal/ApplicationController.php:39
 * @route '/portal/applications'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Portal\ApplicationController::index
 * @see app/Http/Controllers/Portal/ApplicationController.php:39
 * @route '/portal/applications'
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
* @see \App\Http\Controllers\Portal\ApplicationController::create
 * @see app/Http/Controllers/Portal/ApplicationController.php:55
 * @route '/portal/applications/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/portal/applications/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\ApplicationController::create
 * @see app/Http/Controllers/Portal/ApplicationController.php:55
 * @route '/portal/applications/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\ApplicationController::create
 * @see app/Http/Controllers/Portal/ApplicationController.php:55
 * @route '/portal/applications/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Portal\ApplicationController::create
 * @see app/Http/Controllers/Portal/ApplicationController.php:55
 * @route '/portal/applications/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Portal\ApplicationController::create
 * @see app/Http/Controllers/Portal/ApplicationController.php:55
 * @route '/portal/applications/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Portal\ApplicationController::create
 * @see app/Http/Controllers/Portal/ApplicationController.php:55
 * @route '/portal/applications/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Portal\ApplicationController::create
 * @see app/Http/Controllers/Portal/ApplicationController.php:55
 * @route '/portal/applications/create'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\Portal\ApplicationController::store
 * @see app/Http/Controllers/Portal/ApplicationController.php:67
 * @route '/portal/applications'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/portal/applications',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Portal\ApplicationController::store
 * @see app/Http/Controllers/Portal/ApplicationController.php:67
 * @route '/portal/applications'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\ApplicationController::store
 * @see app/Http/Controllers/Portal/ApplicationController.php:67
 * @route '/portal/applications'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Portal\ApplicationController::store
 * @see app/Http/Controllers/Portal/ApplicationController.php:67
 * @route '/portal/applications'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Portal\ApplicationController::store
 * @see app/Http/Controllers/Portal/ApplicationController.php:67
 * @route '/portal/applications'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Portal\ApplicationController::show
 * @see app/Http/Controllers/Portal/ApplicationController.php:80
 * @route '/portal/applications/{application}'
 */
export const show = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/portal/applications/{application}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\ApplicationController::show
 * @see app/Http/Controllers/Portal/ApplicationController.php:80
 * @route '/portal/applications/{application}'
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
* @see \App\Http\Controllers\Portal\ApplicationController::show
 * @see app/Http/Controllers/Portal/ApplicationController.php:80
 * @route '/portal/applications/{application}'
 */
show.get = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Portal\ApplicationController::show
 * @see app/Http/Controllers/Portal/ApplicationController.php:80
 * @route '/portal/applications/{application}'
 */
show.head = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Portal\ApplicationController::show
 * @see app/Http/Controllers/Portal/ApplicationController.php:80
 * @route '/portal/applications/{application}'
 */
    const showForm = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Portal\ApplicationController::show
 * @see app/Http/Controllers/Portal/ApplicationController.php:80
 * @route '/portal/applications/{application}'
 */
        showForm.get = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Portal\ApplicationController::show
 * @see app/Http/Controllers/Portal/ApplicationController.php:80
 * @route '/portal/applications/{application}'
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
* @see \App\Http\Controllers\Portal\ApplicationController::premises
 * @see app/Http/Controllers/Portal/ApplicationController.php:130
 * @route '/portal/applications/{application}/premises'
 */
export const premises = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: premises.url(args, options),
    method: 'put',
})

premises.definition = {
    methods: ["put"],
    url: '/portal/applications/{application}/premises',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Portal\ApplicationController::premises
 * @see app/Http/Controllers/Portal/ApplicationController.php:130
 * @route '/portal/applications/{application}/premises'
 */
premises.url = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return premises.definition.url
            .replace('{application}', parsedArgs.application.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\ApplicationController::premises
 * @see app/Http/Controllers/Portal/ApplicationController.php:130
 * @route '/portal/applications/{application}/premises'
 */
premises.put = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: premises.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Portal\ApplicationController::premises
 * @see app/Http/Controllers/Portal/ApplicationController.php:130
 * @route '/portal/applications/{application}/premises'
 */
    const premisesForm = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: premises.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Portal\ApplicationController::premises
 * @see app/Http/Controllers/Portal/ApplicationController.php:130
 * @route '/portal/applications/{application}/premises'
 */
        premisesForm.put = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: premises.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    premises.form = premisesForm
/**
* @see \App\Http\Controllers\Portal\ApplicationController::technicalPerson
 * @see app/Http/Controllers/Portal/ApplicationController.php:144
 * @route '/portal/applications/{application}/technical-person'
 */
export const technicalPerson = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: technicalPerson.url(args, options),
    method: 'put',
})

technicalPerson.definition = {
    methods: ["put"],
    url: '/portal/applications/{application}/technical-person',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Portal\ApplicationController::technicalPerson
 * @see app/Http/Controllers/Portal/ApplicationController.php:144
 * @route '/portal/applications/{application}/technical-person'
 */
technicalPerson.url = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return technicalPerson.definition.url
            .replace('{application}', parsedArgs.application.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\ApplicationController::technicalPerson
 * @see app/Http/Controllers/Portal/ApplicationController.php:144
 * @route '/portal/applications/{application}/technical-person'
 */
technicalPerson.put = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: technicalPerson.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Portal\ApplicationController::technicalPerson
 * @see app/Http/Controllers/Portal/ApplicationController.php:144
 * @route '/portal/applications/{application}/technical-person'
 */
    const technicalPersonForm = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: technicalPerson.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Portal\ApplicationController::technicalPerson
 * @see app/Http/Controllers/Portal/ApplicationController.php:144
 * @route '/portal/applications/{application}/technical-person'
 */
        technicalPersonForm.put = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: technicalPerson.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    technicalPerson.form = technicalPersonForm
/**
* @see \App\Http\Controllers\Portal\ApplicationController::submit
 * @see app/Http/Controllers/Portal/ApplicationController.php:155
 * @route '/portal/applications/{application}/submit'
 */
export const submit = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(args, options),
    method: 'post',
})

submit.definition = {
    methods: ["post"],
    url: '/portal/applications/{application}/submit',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Portal\ApplicationController::submit
 * @see app/Http/Controllers/Portal/ApplicationController.php:155
 * @route '/portal/applications/{application}/submit'
 */
submit.url = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return submit.definition.url
            .replace('{application}', parsedArgs.application.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\ApplicationController::submit
 * @see app/Http/Controllers/Portal/ApplicationController.php:155
 * @route '/portal/applications/{application}/submit'
 */
submit.post = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Portal\ApplicationController::submit
 * @see app/Http/Controllers/Portal/ApplicationController.php:155
 * @route '/portal/applications/{application}/submit'
 */
    const submitForm = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submit.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Portal\ApplicationController::submit
 * @see app/Http/Controllers/Portal/ApplicationController.php:155
 * @route '/portal/applications/{application}/submit'
 */
        submitForm.post = (args: { application: number | { id: number } } | [application: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submit.url(args, options),
            method: 'post',
        })
    
    submit.form = submitForm
const applications = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
show: Object.assign(show, show),
premises: Object.assign(premises, premises),
technicalPerson: Object.assign(technicalPerson, technicalPerson),
submit: Object.assign(submit, submit),
}

export default applications