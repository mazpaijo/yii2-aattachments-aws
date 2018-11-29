<?php

namespace mazpaijo\attachmentsAws\components;

use kartik\file\FileInput;
use mazpaijo\attachmentsAws\models\UploadForm;
use mazpaijo\attachmentsAws\ModuleTrait;
use yii\base\InvalidConfigException;
use yii\bootstrap\Widget;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Created by PhpStorm.
 * User: Алимжан
 * Date: 13.02.2015
 * Time: 21:18
 */
class AttachmentsInput extends Widget
{
    use ModuleTrait;

    public $id = 'file-input';

    public $model;

    public $pluginOptions = [];

    public $options = [];

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub

        if (empty($this->model)) {
            throw new InvalidConfigException("Property {model} cannot be blank");
        }

        FileHelper::removeDirectory($this->getModule()->getUserDirPath()); // Delete all uploaded files in past

        $this->pluginOptions = array_replace($this->pluginOptions, [
            'uploadUrl' => Url::toRoute('/attachments/file/upload'),
            'uploadAsync' => false
        ]);
        if(!isset($this->pluginOptions['initialPreview'])){
            $this->pluginOptions = array_replace($this->pluginOptions, [
                'initialPreview' => $this->model->isNewRecord ? [] : $this->model->getInitialPreview(),
            ]);
        }
        if(!isset($this->pluginOptions['initialPreviewConfig'])){
            $this->pluginOptions = array_replace($this->pluginOptions, [
                'initialPreviewConfig' => $this->model->isNewRecord ? [] : $this->model->getInitialPreviewConfig(),
            ]);
        }

        $this->options = array_replace($this->options, [
            'id' => $this->id,
            //'multiple' => true
        ]);

        $js = <<<JS
var fileInput = $('#file-input');
var form = fileInput.closest('form');
var filesUploaded = false;
var filesToUpload = 0;
var uploadButtonClicked = false;
//var formSubmit = false;
form.on('beforeSubmit', function(event) { // form submit event
    console.log('submit');
    if (!filesUploaded && filesToUpload) {
        console.log('upload');
        $('#file-input').fileinput('upload').fileinput('lock');

        return false;
    }
});

fileInput.on('filebatchpreupload', function(event, data, previewId, index) {
    uploadButtonClicked = true;
});

//fileInput.on('filebatchuploadcomplete', function(event, files, extra) { // all files successfully uploaded
fileInput.on('filebatchuploadsuccess', function(event, data, previewId, index) {
    filesUploaded = true;
    $('#file-input').fileinput('unlock');
    if (uploadButtonClicked) {
        form.submit();
    } else {
        uploadButtonClicked = false;
    }
});

fileInput.on('filebatchselected', function(event, files) { // there are some files to upload
    filesToUpload = files.length
});

fileInput.on('filecleared', function(event) { // no files to upload
    filesToUpload = 0;
});

JS;

        \Yii::$app->view->registerJs($js);
    }

    public function run()
    {
        $fileinput = FileInput::widget([
            'model' => new UploadForm(),
            'attribute' => 'file[]',
            'options' => $this->options,
            'pluginOptions' => $this->pluginOptions
        ]);

        return Html::tag('div', $fileinput, ['class' => 'form-group']);
    }
}