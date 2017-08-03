<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
    {preventCsrf}

    {capture name='title' append='field_after'}
        <div id="quiz_id" class="row">
            <label for="" class="col col-lg-2 control-label">{lang key='quiz'} {lang key='field_required'}</label>
            <div class="col col-lg-4">
                <select name="quiz_id" id="field_quiz_id">
                    <option value="">{lang key='_select_'}</option>
                    {if !empty($item.quizzes)}
                        {foreach  $item.quizzes as $quiz}
                            <option value="{$quiz.id}"{if !empty($item.quiz_id) && $item.quiz_id == $quiz.id} selected{/if}>{$quiz.title}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
    {/capture}

    {include 'field-type-content-fieldset.tpl' isSystem=true datetime=true}
</form>