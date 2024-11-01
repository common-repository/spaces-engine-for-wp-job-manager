jQuery(document).ready(function ($) {

    if ($('#job-manager-job-dashboard').length > 0) {
        $('.wpe-wps-single-custom').css( 'flex-wrap', 'nowrap' );
    }

    if ($('#wpe-wps-jobs-list').length > 0) {

        $('.job_listing.open').css('order', '-1');
        $('.job_listing.open').find('.single_job_listing').slideDown();

        $('body').on('click', '.job_listing:not(.status-expired) .wpe-wps-job-header', function (e) {
            let job = $(this).closest('.job_listing');
            let single = job.find('.single_job_listing');
            $('.job_listing').not(job).removeClass('open');
            job.toggleClass('open');
            $('.single_job_listing').not(single).slideUp('slow');
            single.slideToggle();

            var uri = window.location.href.toString();
            if (uri.indexOf("?") > 0) {
                var clean_uri = uri.substring(0, uri.indexOf("?"));
                window.history.replaceState({}, document.title, clean_uri);
            }

            return false;
        });

    }

    $('body').on('click', '#wpe-wps-jobs-list .application .application_button', function (e) {
        let job = $(this).next('.application_details');
        job.slideToggle();

        return false;
    });



    $ ( document ).ajaxComplete(function(event,xhr,settings){
        if( settings.url.includes( 'get_listings' ) )  {
            $(".wpe-wps-job-header .salary").each(function (e) {
                let salary = $(this).data('salary');
                if (!salary) {
                    return;
                }

                let locale = $(this).data('locale').replace(/_/g, '-');
                let str = new Intl.NumberFormat(
                    locale,
                    {
                        style: 'currency',
                        currency: $(this).data('currency'),
                        trailingZeroDisplay: 'stripIfInteger'
                    }
                ).format($(this).data('salary'));

                str = str + ' / ' + $(this).data('unit');

                $(this).text(str);
            });
        }
    });

    $(".wpe-wps-job-header .salary").each(function (e) {
        let salary = $(this).data('salary');
        if (!salary) {
            return;
        }

        let locale = $(this).data('locale').replace( new RegExp("_", "g"), '-' );
        let str = new Intl.NumberFormat(
            locale,
            {
                style: 'currency',
                currency: $(this).data('currency'),
                trailingZeroDisplay: 'stripIfInteger'
            }
        ).format($(this).data('salary'));

        str = str + ' / ' + $(this).data('unit');

        $(this).text(str);
    });

    window.BuddyBossThemeJm = {
        init: function () {
            this.jobFilter(),
                this.jobManager(),
                this.jmRelatedSlider(),
                this.jmScaleTable()
        },
        jobFilter: function () {
            $(document).on("click", ".bb-job-filter .job-filter-heading", function (i) {
                $(this).closest(".bb-job-filter").toggleClass("bbj-state")
            })
        },
        jobManager: function () {
            $(".single-job-sidebar .application_button").magnificPopup({
                fixedBgPos: !0,
                fixedContentPos: !0,
                items: {
                    src: ".single-job-sidebar .bb_application_details",
                    type: "inline"
                }
            }),
                $(".single_job_listing .application_button").magnificPopup({
                    fixedBgPos: !0,
                    fixedContentPos: !0,
                    items: {
                        src: ".single_job_listing .bb_application_details",
                        type: "inline"
                    }
                }),
                $("p.resume_submit_wrap input.button").val(function (i, e) {
                    return e.replace(/[^a-z0-9\s]/gi, "")
                }),
            /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) && ($("#submit-job-form #job_deadline").prop("readonly", !0),
                $("#submit-job-form .fieldset-job_deadline .field").append('<span class="jm-clear">x</span>'),
                $(document).on("click", ".jm-clear", function (i) {
                    $("#submit-job-form .fieldset-job_deadline .field #job_deadline").val("")
                })),
                $(document).ajaxComplete(function () {
                    $("form.job_filters .showing_jobs a").each(function () {
                        if (0 === $(this).find("span").length) {
                            var i = $(this)
                                , e = i.text();
                            i.wrapInner("<span></span>"),
                                i.attr("data-balloon-pos", "up"),
                                i.attr("data-balloon", e)
                        }
                    })
                })
        },
        jmRelatedSlider: function () {
            !function () {
                var i = $(".post-related-jobs .job_listings_grid")
                    , e = {
                    infinite: !1,
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    adaptiveHeight: !0,
                    arrows: !0,
                    prevArrow: '<a class="bb-slide-prev"><i class="bb-icon-l bb-icon-angle-right"></i></a>',
                    nextArrow: '<a class="bb-slide-next"><i class="bb-icon-l bb-icon-angle-right"></i></a>'
                };
                if (i.slick(e),
                $(window).width() < 1280)
                    return i.hasClass("slick-initialized") && i.slick("unslick");
                $(window).on("resize", function () {
                    if (!(n(window).width() < 1280))
                        return i.hasClass("slick-initialized") ? void 0 : i.slick(e);
                    i.hasClass("slick-initialized") && i.slick("unslick")
                })
            }()
        },
        jmScaleTable: function () {
            $("#job-manager-alerts table.job-manager-alerts").wrap('<div class="wrap-job-manager-alerts"></div>'),
            $("#job-manager-job-dashboard table.job-manager-jobs").wrap('<div class="wrap-job-manager-jobs"></div>')
        }
    },
        $(document).on("ready", function () {
            BuddyBossThemeJm.init()
        })

});