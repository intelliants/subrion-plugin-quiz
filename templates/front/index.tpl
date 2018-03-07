{if !empty($entry)}
    <div class="q-item-view">
        {$entry.body}

        <p class="text-i text-fade-50 text-center"><span class="js-current-question">{if $entry.questions_page}{$entry.questions_page}{else}0{/if}</span>/{if $entry.questions_num}{$entry.questions_num}{else}0{/if}</p>

        <div class="q-item-view__questions js-load-questions">
            {if !empty($entry.question)}
                {include file='module:quiz/question.tpl'}
            {/if}
        </div>
        <hr>
        <div class="q-item-view__info">
            <div class="pull-left">
                <i class="fa fa-calendar"></i>
                {lang key='posted_on'} {$entry.date_added|date_format:$core.config.date_format} {lang key='by'} {$entry.fullname}
            </div>
            <div class="pull-right">
                <ul class="list-inline share-buttons">
                    <li><a href="https://www.facebook.com/sharer/sharer.php?u={$smarty.const.IA_SELF|escape:'url'}&t={$entry.title}" target="_blank" title="Share on Facebook"><i class="fa fa-facebook-square fa-2x"></i></a></li>
                    <li><a href="https://twitter.com/intent/tweet?source={$smarty.const.IA_SELF|escape:'url'}&text={$entry.title}:{$smarty.const.IA_SELF|escape:'url'}" target="_blank" title="Tweet"><i class="fa fa-twitter-square fa-2x"></i></a></li>
                    <li><a href="https://plus.google.com/share?url={$smarty.const.IA_SELF|escape:'url'}" target="_blank" title="Share on Google+"><i class="fa fa-google-plus-square fa-2x"></i></a></li>
                    <li><a href="http://pinterest.com/pin/create/button/?url={$smarty.const.IA_SELF|escape:'url'}" target="_blank" title="Pin it"><i class="fa fa-pinterest-square fa-2x"></i></a></li>
                </ul>
            </div>
        </div>
    </div>

    {ia_add_media files='js:_IA_URL_modules/quiz/js/front/quiz, css:_IA_URL_modules/quiz/templates/front/css/style'}
{elseif !empty($result)}
    <div class="q-item-view">
        {$result.quiz_completion_text}

        <p class="q-item-view__result">{lang key='your_result'} <span>{$result.evaluation}</span></p>

        <div class="q-item-view__repeat">
            <a href="{$smarty.const.IA_URL}quizzes/{$result.id}-{$result.slug}.html">
                <i class="fa fa-refresh"></i> {lang key='quiz_repeat'}
            </a>
        </div>
    </div>
    {ia_add_media files='css:_IA_URL_modules/quiz/templates/front/css/style'}
{else}
    {if $entries}
        <div class="ia-items">
            {foreach $entries as $entry}
                {include file='module:quiz/list-quizzes.tpl'}
            {/foreach}
        </div>

        {navigation aTotal=$pagination.total aTemplate=$pagination.url aItemsPerPage=$pagination.limit aNumPageItems=5}
    {else}
        <div class="alert alert-info">{lang key='no_quizzes'}</div>
    {/if}
{/if}