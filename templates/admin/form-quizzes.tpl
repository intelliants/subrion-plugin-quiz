<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
    {preventCsrf}

    <input type="hidden" name="id" value="{if isset($item.id)}{$item.id}{/if}">

    {capture name='title' append='field_after'}
        <div id="title_slug" class="row">
            <label for="" class="col col-lg-2 control-label">{lang key='slug'} <a href="#" class="js-tooltip" title="{$tooltips.slug_literal}"><i class="i-info"></i></a></label>
            <div class="col col-lg-4">
                <input type="text" name="title_slug" id="field_title_slug" value="{if isset($item.slug)}{$item.slug}{/if}">
                <p class="help-block text-break-word">{lang key='page_url_will_be'}: <span class="text-danger" id="title_url">{$smarty.const.IA_URL}{if isset($item.slug)}{$item.slug}{/if}</span></p>
            </div>
        </div>
    {/capture}

    {include 'field-type-content-fieldset.tpl' isSystem=true datetime=true}
</form>

{ia_add_media files='js:_IA_URL_modules/quiz/js/admin/quizzes'}