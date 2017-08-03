<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
    {preventCsrf}

    {capture name='title' append='field_after'}
        <div id="question_id" class="row">
            <label for="field_question_id" class="col col-lg-2 control-label">{lang key='question'} {lang key='field_required'}</label>
            <div class="col col-lg-4">
                <select name="question_id" id="field_question_id">
                    <option value="">{lang key='_select_'}</option>
                    {if !empty($item.questions)}
                        {foreach  $item.questions as $question}
                            <option value="{$question.id}"{if !empty($item.question_id) && $item.question_id == $question.id} selected{/if}>
                                {if !empty($question.quiz)}{$question.quiz} | {/if}{$question.title}
                            </option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
    {/capture}

    {capture name='body' append='field_after'}
        <div id="correct_answer" class="row">
            <label class="col col-lg-2 control-label">{lang key='correct_answer'}</label>
            <div class="col col-lg-4">
                {html_radio_switcher name='correct_answer' value=$item.correct_answer|default:0}
            </div>
        </div>
    {/capture}

    {include 'field-type-content-fieldset.tpl' isSystem=true datetime=true}
</form>