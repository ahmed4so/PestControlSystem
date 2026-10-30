import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import checklist from './checklist'
import photos from './photos'
/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::index
* @see app/Http/Controllers/Portal/Company/PremisesController.php:36
* @route '/portal/premises'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/portal/premises',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::index
* @see app/Http/Controllers/Portal/Company/PremisesController.php:36
* @route '/portal/premises'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::index
* @see app/Http/Controllers/Portal/Company/PremisesController.php:36
* @route '/portal/premises'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::index
* @see app/Http/Controllers/Portal/Company/PremisesController.php:36
* @route '/portal/premises'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::index
* @see app/Http/Controllers/Portal/Company/PremisesController.php:36
* @route '/portal/premises'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::index
* @see app/Http/Controllers/Portal/Company/PremisesController.php:36
* @route '/portal/premises'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::index
* @see app/Http/Controllers/Portal/Company/PremisesController.php:36
* @route '/portal/premises'
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
* @see \App\Http\Controllers\Portal\Company\PremisesController::create
* @see app/Http/Controllers/Portal/Company/PremisesController.php:53
* @route '/portal/premises/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/portal/premises/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::create
* @see app/Http/Controllers/Portal/Company/PremisesController.php:53
* @route '/portal/premises/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::create
* @see app/Http/Controllers/Portal/Company/PremisesController.php:53
* @route '/portal/premises/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::create
* @see app/Http/Controllers/Portal/Company/PremisesController.php:53
* @route '/portal/premises/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::create
* @see app/Http/Controllers/Portal/Company/PremisesController.php:53
* @route '/portal/premises/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::create
* @see app/Http/Controllers/Portal/Company/PremisesController.php:53
* @route '/portal/premises/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::create
* @see app/Http/Controllers/Portal/Company/PremisesController.php:53
* @route '/portal/premises/create'
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
* @see \App\Http\Controllers\Portal\Company\PremisesController::store
* @see app/Http/Controllers/Portal/Company/PremisesController.php:60
* @route '/portal/premises'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/portal/premises',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::store
* @see app/Http/Controllers/Portal/Company/PremisesController.php:60
* @route '/portal/premises'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::store
* @see app/Http/Controllers/Portal/Company/PremisesController.php:60
* @route '/portal/premises'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::store
* @see app/Http/Controllers/Portal/Company/PremisesController.php:60
* @route '/portal/premises'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::store
* @see app/Http/Controllers/Portal/Company/PremisesController.php:60
* @route '/portal/premises'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::edit
* @see app/Http/Controllers/Portal/Company/PremisesController.php:70
* @route '/portal/premises/{premises}/edit'
*/
export const edit = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/portal/premises/{premises}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::edit
* @see app/Http/Controllers/Portal/Company/PremisesController.php:70
* @route '/portal/premises/{premises}/edit'
*/
edit.url = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { premises: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { premises: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            premises: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        premises: typeof args.premises === 'object'
        ? args.premises.id
        : args.premises,
    }

    return edit.definition.url
            .replace('{premises}', parsedArgs.premises.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::edit
* @see app/Http/Controllers/Portal/Company/PremisesController.php:70
* @route '/portal/premises/{premises}/edit'
*/
edit.get = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::edit
* @see app/Http/Controllers/Portal/Company/PremisesController.php:70
* @route '/portal/premises/{premises}/edit'
*/
edit.head = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::edit
* @see app/Http/Controllers/Portal/Company/PremisesController.php:70
* @route '/portal/premises/{premises}/edit'
*/
const editForm = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::edit
* @see app/Http/Controllers/Portal/Company/PremisesController.php:70
* @route '/portal/premises/{premises}/edit'
*/
editForm.get = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::edit
* @see app/Http/Controllers/Portal/Company/PremisesController.php:70
* @route '/portal/premises/{premises}/edit'
*/
editForm.head = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:75
* @route '/portal/premises/{premises}'
*/
export const update = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/portal/premises/{premises}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:75
* @route '/portal/premises/{premises}'
*/
update.url = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { premises: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { premises: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            premises: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        premises: typeof args.premises === 'object'
        ? args.premises.id
        : args.premises,
    }

    return update.definition.url
            .replace('{premises}', parsedArgs.premises.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:75
* @route '/portal/premises/{premises}'
*/
update.put = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:75
* @route '/portal/premises/{premises}'
*/
update.patch = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:75
* @route '/portal/premises/{premises}'
*/
const updateForm = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:75
* @route '/portal/premises/{premises}'
*/
updateForm.put = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:75
* @route '/portal/premises/{premises}'
*/
updateForm.patch = (args: { premises: number | { id: number } } | [premises: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const premises = {
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    checklist: Object.assign(checklist, checklist),
    photos: Object.assign(photos, photos),
}

export default premises