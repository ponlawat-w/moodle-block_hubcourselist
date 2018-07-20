require(['jquery'], function ($) {
    $(document).ready(function() {
        var boost = M.cfg.theme === 'boost';

        var $body = $('body');
        var isfrontpage = $body.hasClass('pagelayout-frontpage');
        if (isfrontpage) {
            var $wholecontent = boost ? $('aside[data-block="hubcourselist"]') : $('div.block.block_hubcourselist');
            console.log($wholecontent);
            var $target = boost ? $('section#region-main .card.card-block').find('div[role="main"]') : $('div[role="main"]');
            $target.prepend($wholecontent);
            var otherblocks = $('aside.block[data-block!="hubcourselist"]').length;
            if (!otherblocks) {
                $('section#region-main').removeClass('has-blocks');
            }
        }

        var $spinner = $('#block_hubcourselist_spinner');
        var $amountselect = $('#block_hubcourselist_amountselect');
        var $subjectselect = $('#block_hubcourselist_subjectselect');
        var $keywordinput = $('#block_hubcourselist_keywordinput');
        var $clearkeywordbtn = $('#block_hubcourselist_clearkeywordbtn');
        var $table = $('#block_hubcourselist_table');
        var $status = $('#block_hubcourselist_status');
        var $pagination = $('#block_hubcourselist_pagination');

        if (!boost) {
            $('#block_hubcourselist_keywords').removeClass('row');
            $('#block_hubcourelist_statusbar').removeClass('row');
            $('#block_hubcourselist_amountselect_container').addClass('hcl-inline');
            $('#block_hubcourselist_subjectselect_container').addClass('hcl-inline');
            $('#block_hubcourselist_keywordinput_container').addClass('hcl-inline');
            $status.removeClass('row');
        }

        var $clearkeywordbtn_prototype = $clearkeywordbtn.clone();

        var pagination = {
            max: 1,
            current: 1,
            setmax: function(newmax) {
                this.max = newmax;
                if (newmax > 0) {
                    this.current = 1;
                }
                this.update();
            },
            next: function() {
                if (this.current < this.max) {
                    this.current++;
                    this.updateactive();
                }
            },
            previous: function() {
                if (this.current > 1) {
                    this.current--;
                    this.updateactive();
                }
            },
            first: function() {
                if (this.max > 0) {
                    this.current = 1;
                    this.updateactive();
                }
            },
            last: function() {
                if (this.max > 0) {
                    this.current = this.max;
                    this.updateactive();
                }
            },
            set: function(page) {
                if (page > 0 && page <= this.max) {
                    this.current = page;
                    this.updateactive();
                }
            },
            update: function() {
                $pagination.find('.page-number').remove();

                for (var page = 1; page <= this.max; page++) {
                    $('<li>').attr('apiservice-page', page).addClass('page-item page-number').html(
                        $('<a>').attr('href', 'javascript:void(0);').addClass('page-link').html(page)
                    ).insertBefore($pagination.find('.page-next'));
                }

                this.updateactive();
            },
            updateactive: function() {
                $pagination.find('.page-item').removeClass('active');
                $pagination.find('.page-number[apiservice-page="' + this.current + '"]').addClass('active');
            }
        };

        var querydataservice = {
            sortby: 'timecreated',
            asc: false,
            create: function() {
                var querydata = {
                    subject: $subjectselect.val(),
                    keyword: encodeURI($keywordinput.val().trim()),
                    maxresult: $amountselect.val(),
                    sortby: this.sortby ? this.sortby : 'timecreated',
                    sorttype: this.asc ? 'asc' : 'desc',
                    page: pagination.current
                };

                var queryarr = [];
                $.each(querydata, function (key, value) {
                    if (key === 'create') {
                        return true;
                    }

                    queryarr.push(key + '=' + value);
                });

                return queryarr.join('&');
            }
        };

        var apiservice = {
            courses: [],
            records: 0,
            load: function() {
                $spinner.show();
                $status.html(M.str.block_hubcourselist.loading);
                $table.find('thead th').removeClass('bg-primary').find('i').remove();
                $table.find('thead th[data-sortby="' + querydataservice.sortby + '"]').addClass('bg-primary')
                    .append(' ')
                    .append($('<i>').addClass(querydataservice.asc ? 'fa fa-arrow-down' : 'fa fa-arrow-up'));

                $.ajax(M.cfg.wwwroot + '/blocks/hubcourselist/api.php?' + querydataservice.create(), {
                    method: 'GET',
                    success: function (response) {
                        apiservice.records = parseInt(response.records);
                        apiservice.courses = response.results;
                        pagination.setmax(response.maxpage > 0 ? response.maxpage : 1);
                        pagination.set(response.currentpage > 0 ? response.currentpage : 1);
                        table.update();
                        $status.html(response.status);

                        if (apiservice.courses.length > 0) {
                            $status.removeClass('no-result');
                            $table.show();
                            $pagination.show();
                        } else {
                            $status.addClass('no-result');
                            $table.hide();
                            $pagination.hide();
                        }

                        $spinner.hide();
                    },
                    error: function () {
                        $spinner.hide();
                    }
                })
            }
        };

        var table = {
            createcell: function(text, url) {
                return $('<td>').html(
                    $('<a>').attr('href', url).html(text)
                ).attr('data-url', url).click(function() {
                    window.location = $(this).attr('data-url');
                });
            },
            shortenversion: function(release) {
                if (!release) {
                    return '';
                }
                var extracted = release.split('.');
                if (extracted.length < 1) {
                    return '';
                }

                return extracted.length < 2 ? extracted[0] : extracted[0] + '.' + extracted[1].split(' ')[0];
            },
            update: function() {
                $table.find('tbody tr').remove();

                for (var ci = 0; ci < apiservice.courses.length; ci++) {
                    var course = apiservice.courses[ci];
                    var courseurl = M.cfg.wwwroot + '/course/view.php?id=' + course.id;

                    $table.find('tbody').append(
                        $('<tr>').html('').append(
                            this.createcell(course.displaytimecreated, courseurl).attr('title', course.fulldisplaytimecreated)
                        ).append(
                            this.createcell(course.coursefullname, courseurl).attr('title', course.courseshortname)
                        ).append(
                            this.createcell(this.shortenversion(course.moodlerelease), courseurl).attr('title', course.moodlerelease + ' - ' + course.moodleversion)
                        ).append(
                            this.createcell(course.userfullname, M.cfg.wwwroot + '/user/profile.php?id=' + course.userid)
                        )
                    );
                }
            }
        };

        var keywordinputinterval = null;
        var keyword = {
            apply: function() {
                pagination.current = 1;

                if ($keywordinput.val() !== '' && !$keywordinput.siblings('#block_hubcourselist_clearkeywordbtn').length) {
                    $clearkeywordbtn_prototype.clone().insertAfter($keywordinput).click(function() {
                        pagination.current = 1;
                        $keywordinput.val('');
                        apiservice.load();
                        $(this).remove();
                    });
                } else if ($keywordinput.val() === '') {
                    $keywordinput.siblings('#block_hubcourselist_clearkeywordbtn').remove();
                }

                clearTimeout(keywordinputinterval);
                keywordinputinterval = setTimeout(function() {
                    apiservice.load();
                }, 500);
            }
        };

        $clearkeywordbtn.remove();

        $amountselect.change(function() {
            pagination.current = 1;
            apiservice.load();
        });

        $subjectselect.change(function() {
            apiservice.load();
        });

        $keywordinput.bind('input', keyword.apply);

        $table.find('thead th').click(function() {
            if (querydataservice.sortby !== $(this).attr('data-sortby')) {
                querydataservice.asc = true;
                querydataservice.sortby = $(this).attr('data-sortby');
            } else {
                querydataservice.asc = !querydataservice.asc;
            }
            apiservice.load();
        });

        $body.on('click', '.page-first a', function() {
           pagination.first();
           apiservice.load();
        });
        $body.on('click', '.page-previous a', function() {
           pagination.previous();
           apiservice.load();
        });
        $body.on('click', '.page-next a', function() {
           pagination.next();
           apiservice.load();
        });
        $body.on('click', '.page-last a', function() {
           pagination.last();
           apiservice.load();
        });
        $body.on('click', '.page-number a', function() {
            pagination.set($(this).parent('.page-number').attr('apiservice-page'));
            apiservice.load();
        });

        apiservice.load();
    });
});