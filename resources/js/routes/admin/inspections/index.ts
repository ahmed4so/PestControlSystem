import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\InspectionController::show
 * @see app/Http/Controllers/Admin/InspectionController.php:47
 * @route '/admin/inspections/{inspection}'
 */
export const show = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/inspections/{inspection}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\InspectionController::show
 * @see app/Http/Controllers/Admin/InspectionController.php:47
 * @route '/admin/inspections/{inspection}'
 */
show.url = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inspection: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inspection: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inspection: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inspection: typeof args.inspection === 'object'
                ? args.inspection.id
                : args.inspection,
                }

    return show.definition.url
            .replace('{inspection}', parsedArgs.inspection.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InspectionController::show
 * @see app/Http/Controllers/Admin/InspectionController.php:47
 * @route '/admin/inspections/{inspection}'
 */
show.get = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InspectionController::show
 * @see app/Http/Controllers/Admin/InspectionController.php:47
 * @route '/admin/inspections/{inspection}'
 */
show.head = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\InspectionController::show
 * @see app/Http/Controllers/Admin/InspectionController.php:47
 * @route '/admin/inspections/{inspection}'
 */
    const showForm = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\InspectionController::show
 * @see app/Http/Controllers/Admin/InspectionController.php:47
 * @route '/admin/inspections/{inspection}'
 */
        showForm.get = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\InspectionController::show
 * @see app/Http/Controllers/Admin/InspectionController.php:47
 * @route '/admin/inspections/{inspection}'
 */
        showForm.head = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\InspectionController::report
 * @see app/Http/Controllers/Admin/InspectionController.php:107
 * @route '/admin/inspections/{inspection}/report'
 */
export const report = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: report.url(args, options),
    method: 'put',
})

report.definition = {
    methods: ["put"],
    url: '/admin/inspections/{inspection}/report',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\InspectionController::report
 * @see app/Http/Controllers/Admin/InspectionController.php:107
 * @route '/admin/inspections/{inspection}/report'
 */
report.url = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inspection: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inspection: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inspection: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inspection: typeof args.inspection === 'object'
                ? args.inspection.id
                : args.inspection,
                }

    return report.definition.url
            .replace('{inspection}', parsedArgs.inspection.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InspectionController::report
 * @see app/Http/Controllers/Admin/InspectionController.php:107
 * @route '/admin/inspections/{inspection}/report'
 */
report.put = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: report.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Admin\InspectionController::report
 * @see app/Http/Controllers/Admin/InspectionController.php:107
 * @route '/admin/inspections/{inspection}/report'
 */
    const reportForm = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: report.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InspectionController::report
 * @see app/Http/Controllers/Admin/InspectionController.php:107
 * @route '/admin/inspections/{inspection}/report'
 */
        reportForm.put = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: report.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    report.form = reportForm
/**
* @see \App\Http\Controllers\Admin\InspectionController::media
 * @see app/Http/Controllers/Admin/InspectionController.php:127
 * @route '/admin/inspections/{inspection}/media'
 */
export const media = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: media.url(args, options),
    method: 'post',
})

media.definition = {
    methods: ["post"],
    url: '/admin/inspections/{inspection}/media',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\InspectionController::media
 * @see app/Http/Controllers/Admin/InspectionController.php:127
 * @route '/admin/inspections/{inspection}/media'
 */
media.url = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inspection: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inspection: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inspection: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inspection: typeof args.inspection === 'object'
                ? args.inspection.id
                : args.inspection,
                }

    return media.definition.url
            .replace('{inspection}', parsedArgs.inspection.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InspectionController::media
 * @see app/Http/Controllers/Admin/InspectionController.php:127
 * @route '/admin/inspections/{inspection}/media'
 */
media.post = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: media.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\InspectionController::media
 * @see app/Http/Controllers/Admin/InspectionController.php:127
 * @route '/admin/inspections/{inspection}/media'
 */
    const mediaForm = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: media.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InspectionController::media
 * @see app/Http/Controllers/Admin/InspectionController.php:127
 * @route '/admin/inspections/{inspection}/media'
 */
        mediaForm.post = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: media.url(args, options),
            method: 'post',
        })
    
    media.form = mediaForm
/**
* @see \App\Http\Controllers\Admin\InspectionController::visit
 * @see app/Http/Controllers/Admin/InspectionController.php:153
 * @route '/admin/inspections/{inspection}/visit'
 */
export const visit = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: visit.url(args, options),
    method: 'post',
})

visit.definition = {
    methods: ["post"],
    url: '/admin/inspections/{inspection}/visit',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\InspectionController::visit
 * @see app/Http/Controllers/Admin/InspectionController.php:153
 * @route '/admin/inspections/{inspection}/visit'
 */
visit.url = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inspection: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inspection: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inspection: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inspection: typeof args.inspection === 'object'
                ? args.inspection.id
                : args.inspection,
                }

    return visit.definition.url
            .replace('{inspection}', parsedArgs.inspection.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InspectionController::visit
 * @see app/Http/Controllers/Admin/InspectionController.php:153
 * @route '/admin/inspections/{inspection}/visit'
 */
visit.post = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: visit.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\InspectionController::visit
 * @see app/Http/Controllers/Admin/InspectionController.php:153
 * @route '/admin/inspections/{inspection}/visit'
 */
    const visitForm = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: visit.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InspectionController::visit
 * @see app/Http/Controllers/Admin/InspectionController.php:153
 * @route '/admin/inspections/{inspection}/visit'
 */
        visitForm.post = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: visit.url(args, options),
            method: 'post',
        })
    
    visit.form = visitForm
/**
* @see \App\Http\Controllers\Admin\InspectionController::reschedule
 * @see app/Http/Controllers/Admin/InspectionController.php:170
 * @route '/admin/inspections/{inspection}/reschedule'
 */
export const reschedule = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reschedule.url(args, options),
    method: 'post',
})

reschedule.definition = {
    methods: ["post"],
    url: '/admin/inspections/{inspection}/reschedule',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\InspectionController::reschedule
 * @see app/Http/Controllers/Admin/InspectionController.php:170
 * @route '/admin/inspections/{inspection}/reschedule'
 */
reschedule.url = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inspection: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inspection: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inspection: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inspection: typeof args.inspection === 'object'
                ? args.inspection.id
                : args.inspection,
                }

    return reschedule.definition.url
            .replace('{inspection}', parsedArgs.inspection.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InspectionController::reschedule
 * @see app/Http/Controllers/Admin/InspectionController.php:170
 * @route '/admin/inspections/{inspection}/reschedule'
 */
reschedule.post = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reschedule.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\InspectionController::reschedule
 * @see app/Http/Controllers/Admin/InspectionController.php:170
 * @route '/admin/inspections/{inspection}/reschedule'
 */
    const rescheduleForm = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reschedule.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InspectionController::reschedule
 * @see app/Http/Controllers/Admin/InspectionController.php:170
 * @route '/admin/inspections/{inspection}/reschedule'
 */
        rescheduleForm.post = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reschedule.url(args, options),
            method: 'post',
        })
    
    reschedule.form = rescheduleForm
/**
* @see \App\Http\Controllers\Admin\InspectionController::sign
 * @see app/Http/Controllers/Admin/InspectionController.php:193
 * @route '/admin/inspections/{inspection}/sign'
 */
export const sign = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sign.url(args, options),
    method: 'post',
})

sign.definition = {
    methods: ["post"],
    url: '/admin/inspections/{inspection}/sign',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\InspectionController::sign
 * @see app/Http/Controllers/Admin/InspectionController.php:193
 * @route '/admin/inspections/{inspection}/sign'
 */
sign.url = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inspection: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inspection: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inspection: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inspection: typeof args.inspection === 'object'
                ? args.inspection.id
                : args.inspection,
                }

    return sign.definition.url
            .replace('{inspection}', parsedArgs.inspection.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InspectionController::sign
 * @see app/Http/Controllers/Admin/InspectionController.php:193
 * @route '/admin/inspections/{inspection}/sign'
 */
sign.post = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sign.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\InspectionController::sign
 * @see app/Http/Controllers/Admin/InspectionController.php:193
 * @route '/admin/inspections/{inspection}/sign'
 */
    const signForm = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sign.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InspectionController::sign
 * @see app/Http/Controllers/Admin/InspectionController.php:193
 * @route '/admin/inspections/{inspection}/sign'
 */
        signForm.post = (args: { inspection: number | { id: number } } | [inspection: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sign.url(args, options),
            method: 'post',
        })
    
    sign.form = signForm
/**
* @see \App\Http\Controllers\Admin\InspectionController::file
 * @see app/Http/Controllers/Admin/InspectionController.php:209
 * @route '/admin/inspections/{inspection}/media/{media}'
 */
export const file = (args: { inspection: number | { id: number }, media: number | { id: number } } | [inspection: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: file.url(args, options),
    method: 'get',
})

file.definition = {
    methods: ["get","head"],
    url: '/admin/inspections/{inspection}/media/{media}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\InspectionController::file
 * @see app/Http/Controllers/Admin/InspectionController.php:209
 * @route '/admin/inspections/{inspection}/media/{media}'
 */
file.url = (args: { inspection: number | { id: number }, media: number | { id: number } } | [inspection: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    inspection: args[0],
                    media: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inspection: typeof args.inspection === 'object'
                ? args.inspection.id
                : args.inspection,
                                media: typeof args.media === 'object'
                ? args.media.id
                : args.media,
                }

    return file.definition.url
            .replace('{inspection}', parsedArgs.inspection.toString())
            .replace('{media}', parsedArgs.media.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InspectionController::file
 * @see app/Http/Controllers/Admin/InspectionController.php:209
 * @route '/admin/inspections/{inspection}/media/{media}'
 */
file.get = (args: { inspection: number | { id: number }, media: number | { id: number } } | [inspection: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: file.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InspectionController::file
 * @see app/Http/Controllers/Admin/InspectionController.php:209
 * @route '/admin/inspections/{inspection}/media/{media}'
 */
file.head = (args: { inspection: number | { id: number }, media: number | { id: number } } | [inspection: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: file.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\InspectionController::file
 * @see app/Http/Controllers/Admin/InspectionController.php:209
 * @route '/admin/inspections/{inspection}/media/{media}'
 */
    const fileForm = (args: { inspection: number | { id: number }, media: number | { id: number } } | [inspection: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: file.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\InspectionController::file
 * @see app/Http/Controllers/Admin/InspectionController.php:209
 * @route '/admin/inspections/{inspection}/media/{media}'
 */
        fileForm.get = (args: { inspection: number | { id: number }, media: number | { id: number } } | [inspection: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: file.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\InspectionController::file
 * @see app/Http/Controllers/Admin/InspectionController.php:209
 * @route '/admin/inspections/{inspection}/media/{media}'
 */
        fileForm.head = (args: { inspection: number | { id: number }, media: number | { id: number } } | [inspection: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: file.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    file.form = fileForm
const inspections = {
    show: Object.assign(show, show),
report: Object.assign(report, report),
media: Object.assign(media, media),
visit: Object.assign(visit, visit),
reschedule: Object.assign(reschedule, reschedule),
sign: Object.assign(sign, sign),
file: Object.assign(file, file),
}

export default inspections