<?php

/**
 * Add to classes you want changes recorded for
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class ChangeRecordable extends DataExtension
{

    public $dataChangeTrackService;

    public static $dependencies = array(
        'dataChangeTrackService'        => '%$DataChangeTrackService',
    );
    
    private static $ignored_fields = array();
    
    protected $isNewObject = false;
    protected $changeType = 'Change';

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->owner->isInDB()) {
            $this->dataChangeTrackService->track($this->owner, $this->changeType);
        } else {
            $this->isNewObject = true;
            $this->changeType = 'New';
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->isNewObject) {
            $this->dataChangeTrackService->track($this->owner, $this->changeType);
            $this->isNewObject = false;
        }
    }
        
    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        $this->dataChangeTrackService->track($this->owner, 'Delete');
    }

    public function getIgnoredFields()
    {
        $ignored = Config::inst()->get('ChangeRecordable', 'ignored_fields');
        $class = $this->owner->ClassName;
        if (isset($ignored[$class])) {
            return array_combine($ignored[$class], $ignored[$class]);
        }
    }
}
