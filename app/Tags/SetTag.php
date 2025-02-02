<?php
namespace App\Tags;

use App\Classes\LemurLog;
use App\Models\Conversation;

/**
 * Class SetTag
 * @package App\Tags
 * Documentation on this tag, examples and explanation
 * see: https://docs.lemurengine.com/aiml.html
 */
class SetTag extends AimlTag
{
    protected $tagName = "Set";

    /**
     * SetTag Constructor.
     * @param Conversation $conversation
     * @param $attributes
     */
    public function __construct(Conversation $conversation, $attributes = [])
    {
        parent::__construct($conversation, $attributes);
    }


    /**
     * when we close the <set> tag we need to decide if we want
     */
    public function closeTag()
    {


        LemurLog::debug(
            __FUNCTION__,
            [
                'conversation_id'=>$this->conversation->id,
                'turn_id'=>$this->conversation->currentTurnId(),
                'tag_id'=>$this->getTagId(),
                'attributes'=>$this->getAttributes()
            ]
        );

        $contents = $this->getCurrentTagContents(true);

        if ($this->hasAttributes() &&
                $this->checkIfParentAttributeValue('Li', 'LI_STATUS', 'true')) {
            //if this has a parent Li tag with a LI_STATUS = false then we cant set this...

            if ($this->hasAttribute('NAME')) {
                $name = $this->getAttribute('NAME');
                $this->conversation->setGlobalProperty($name, $contents);

                //in addition...
                if ($name == 'topic') {
                    //set directly in the db...
                    $this->conversation->setGlobalProperty('topic', $contents);
                }

                $this->buildResponse($contents);
            } elseif ($this->hasAttribute('VAR')) {
                $name = $this->getAttribute('VAR');
                $this->conversation->setVar($name, $contents);

                $this->buildResponse($contents);
            }
        } else {
            //todo
            LemurLog::warn(
                'Not setting',
                [
                    'conversation_id'=>$this->conversation->id,
                    'turn_id'=>$this->conversation->currentTurnId(),
                    'tag_id'=>$this->getTagId()
                ]
            );
        }
    }




    public function checkIfParentAttributeValue($tagName, $attribute, $value)
    {



        $previousObjectArr = $this->getPreviousTagByNames([$tagName]);

        if (!empty($previousObjectArr)) {
            $previousObject = $previousObjectArr['tag'];

            if ($previousObject->hasAttribute($attribute)) {
                return  $previousObject->checkAttribute($attribute, $value);
            }
        }

        return true;
    }
}
