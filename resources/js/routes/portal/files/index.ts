import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Portal\Company\FileController::document
 * @see app/Http/Controllers/Portal/Company/FileController.php:19
 * @route '/portal/files/documents/{document}'
 */
export const document = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: document.url(args, options),
    method: 'get',
})

document.definition = {
    methods: ["get","head"],
    url: '/portal/files/documents/{document}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Company\FileController::document
 * @see app/Http/Controllers/Portal/Company/FileController.php:19
 * @route '/portal/files/documents/{document}'
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
* @see \App\Http\Controllers\Portal\Company\FileController::document
 * @see app/Http/Controllers/Portal/Company/FileController.php:19
 * @route '/portal/files/documents/{document}'
 */
document.get = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: document.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Portal\Company\FileController::document
 * @see app/Http/Controllers/Portal/Company/FileController.php:19
 * @route '/portal/files/documents/{document}'
 */
document.head = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: document.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Portal\Company\FileController::document
 * @see app/Http/Controllers/Portal/Company/FileController.php:19
 * @route '/portal/files/documents/{document}'
 */
    const documentForm = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: document.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Portal\Company\FileController::document
 * @see app/Http/Controllers/Portal/Company/FileController.php:19
 * @route '/portal/files/documents/{document}'
 */
        documentForm.get = (args: { document: number | { id: number } } | [document: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: document.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Portal\Company\FileController::document
 * @see app/Http/Controllers/Portal/Company/FileController.php:19
 * @route '/portal/files/documents/{document}'
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
const files = {
    document: Object.assign(document, document),
}

export default files