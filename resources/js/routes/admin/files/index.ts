import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\FileController::document
* @see app/Http/Controllers/Admin/FileController.php:31
* @route '/admin/files/documents/{document}'
*/
export const document = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: document.url(args, options),
    method: 'get',
})

document.definition = {
    methods: ["get","head"],
    url: '/admin/files/documents/{document}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\FileController::document
* @see app/Http/Controllers/Admin/FileController.php:31
* @route '/admin/files/documents/{document}'
*/
document.url = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { document: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { document: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            document: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        document: typeof args.document === 'object'
        ? args.document.id
        : args.document,
    }

    return document.definition.url
            .replace('{document}', parsedArgs.document.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\FileController::document
* @see app/Http/Controllers/Admin/FileController.php:31
* @route '/admin/files/documents/{document}'
*/
document.get = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: document.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\FileController::document
* @see app/Http/Controllers/Admin/FileController.php:31
* @route '/admin/files/documents/{document}'
*/
document.head = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: document.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\FileController::document
* @see app/Http/Controllers/Admin/FileController.php:31
* @route '/admin/files/documents/{document}'
*/
const documentForm = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: document.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\FileController::document
* @see app/Http/Controllers/Admin/FileController.php:31
* @route '/admin/files/documents/{document}'
*/
documentForm.get = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: document.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\FileController::document
* @see app/Http/Controllers/Admin/FileController.php:31
* @route '/admin/files/documents/{document}'
*/
documentForm.head = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: document.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

document.form = documentForm

/**
* @see \App\Http\Controllers\Admin\FileController::photo
* @see app/Http/Controllers/Admin/FileController.php:41
* @route '/admin/files/photos/{photo}'
*/
export const photo = (args: { photo: number | { id: number } } | [photo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: photo.url(args, options),
    method: 'get',
})

photo.definition = {
    methods: ["get","head"],
    url: '/admin/files/photos/{photo}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\FileController::photo
* @see app/Http/Controllers/Admin/FileController.php:41
* @route '/admin/files/photos/{photo}'
*/
photo.url = (args: { photo: number | { id: number } } | [photo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { photo: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { photo: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            photo: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        photo: typeof args.photo === 'object'
        ? args.photo.id
        : args.photo,
    }

    return photo.definition.url
            .replace('{photo}', parsedArgs.photo.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\FileController::photo
* @see app/Http/Controllers/Admin/FileController.php:41
* @route '/admin/files/photos/{photo}'
*/
photo.get = (args: { photo: number | { id: number } } | [photo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\FileController::photo
* @see app/Http/Controllers/Admin/FileController.php:41
* @route '/admin/files/photos/{photo}'
*/
photo.head = (args: { photo: number | { id: number } } | [photo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: photo.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\FileController::photo
* @see app/Http/Controllers/Admin/FileController.php:41
* @route '/admin/files/photos/{photo}'
*/
const photoForm = (args: { photo: number | { id: number } } | [photo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\FileController::photo
* @see app/Http/Controllers/Admin/FileController.php:41
* @route '/admin/files/photos/{photo}'
*/
photoForm.get = (args: { photo: number | { id: number } } | [photo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\FileController::photo
* @see app/Http/Controllers/Admin/FileController.php:41
* @route '/admin/files/photos/{photo}'
*/
photoForm.head = (args: { photo: number | { id: number } } | [photo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photo.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

photo.form = photoForm

const files = {
    document: Object.assign(document, document),
    photo: Object.assign(photo, photo),
}

export default files