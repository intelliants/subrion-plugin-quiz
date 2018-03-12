<div class="q-item-view__question">
    <h4 class="q-item-view__title m-b">
        {$entry.question.title|escape}
    </h4>

    {if !empty($entry.question.body)}
        <div class="q-item-view__body m-b">
            {$entry.question.body}
        </div>
    {/if}

    {if !empty($entry.question.pictures)}
        <div class="q-item-view__image m-b">
            {ia_image file=$entry.question.pictures[0] type='large' title=$entry.question.title|escape class='img-responsive'}
        </div>
    {/if}

    {if !empty($entry.question.answers)}
        <form action="{$smarty.const.IA_SELF}" class="js-answer-form" data-quiz-id="{$entry.id}" data-next-question="{$entry.question.next_id}">
            <ul class="list-unstyled">
                {foreach $entry.question.answers as $answer}
                    <li class="js-answer-item{if $answer.correct_answer} correct-answer{/if}" data-answer-id="{$answer.id}">
                        <h5 class="q-item-view-answer__title js-answer-title">
                            <i class="fa fa-circle-o"></i>
                            {$answer.title}
                            <span class="q-item-view__stats js-answer-stats" style="display: none;">
                                <i class="fa fa-bar-chart"></i>
                                <span>{$answer.stats}</span>%
                            </span>
                        </h5>
                        {if !empty($answer.body)}
                            <div class="q-item-view-answer__body js-answer-body" style="display: none;">
                                {$answer.body}
                            </div>
                        {/if}
                    </li>
                {/foreach}
            </ul>
            <div class="form-group text-center q-item-view__btn js-answer-btn" style="display: none;">
                <button type="submit" class="btn btn-primary">
                    {if $entry.question.next_id > 0}
                        {lang key='next_question'}
                    {else}
                        {lang key='submit_and_finish'}
                    {/if}
                </button>
            </div>
        </form>
    {/if}
</div>