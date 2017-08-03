$(function() {
    intelli.cookie.write('correct_answers_num', 0);
    intelli.cookie.write('quiz_id', $('.js-answer-form').data('quizId'));

    $('.js-load-questions').on('mouseenter', '.js-answer-title', function() {
        $(this).closest('ul').hasClass('processed') || $(this).find('.fa-circle-o').removeClass('fa-circle-o').addClass('fa-dot-circle-o');
    });
    $('.js-load-questions').on('mouseleave', '.js-answer-title', function() {
        $(this).closest('ul').hasClass('processed') || $(this).find('.fa-dot-circle-o').removeClass('fa-dot-circle-o').addClass('fa-circle-o');
    });

    $('.js-load-questions').on('click', '.js-answer-title', function(e) {
        e.preventDefault();

        var $wrapper = $(this).closest('ul');

        $wrapper.css('pointerEvents', 'none');

        if (!$wrapper.hasClass('processed')) {
            var $this = $(this),
                $stats = $this.parent().find('.js-answer-stats'),
                $body = $this.parent().find('.js-answer-body'),
                answerIcon = 'exclamation',
                answerStyle = 'text-danger',
                answerId = $this.parent().data('answer-id'),
                url = intelli.config.ia_url + 'quizzes/index.json';

            $.get(url, {action: 'update-clicks-num', id: answerId}, function(response) {
                if (response.length) {
                    $wrapper.addClass('processed').css('pointerEvents', 'auto');

                    if ($stats.is(':hidden')) {
                        $('.js-load-questions .js-answer-stats').fadeIn(300);

                        if ($body.length) {
                            $body.slideDown(300);
                        }
                    }

                    if ($this.parent().hasClass('correct-answer')) {
                        answerIcon = 'check';
                        answerStyle = 'text-success';

                        intelli.cookie.write('correct_answers_num', parseInt(intelli.cookie.read('correct_answers_num')) + 1);
                    } else {
                        $('.js-load-questions .correct-answer .js-answer-title').addClass('text-success')
                            .find('.fa:not(.fa-bar-chart)').removeAttr('class').attr('class', 'fa fa-check');
                    }

                    $this.addClass(answerStyle);
                    $this.find('.fa:not(.fa-bar-chart)').removeAttr('class').attr('class', 'fa fa-' + answerIcon);

                    $('.js-load-questions .js-answer-btn').fadeIn(300);

                    $.each(response, function(index, value) {
                        $('.js-load-questions .js-answer-item[data-answer-id="' + value.id + '"] .js-answer-stats > span').text(value.stats);
                    });
                }
            });
        }
    });

    $('.js-load-questions').on('submit', '.js-answer-form', function(e) {
        e.preventDefault();

        var $form = $(this),
            url = intelli.config.ia_url + 'quizzes/index.json',
            quizId = $form.data('quizId'),
            nextQuestionId = $form.data('nextQuestion');

        if (Boolean(nextQuestionId)) {
            $.get(url, {action: 'load-question', id: nextQuestionId}, function(response) {
                if (response.html) {
                    $('.js-load-questions').html(response.html);
                    $('.js-current-question').text(parseInt($('.js-current-question').text()) + 1);

                    $('html, body').animate({
                        scrollTop: $('.js-load-questions').offset().top - 100
                    }, 300);
                }
            });
        } else {
            location.href = intelli.config.ia_url + 'quizzes/finish/' + quizId + '/'
        }
    });
});