<?php
/**
 * Created by PhpStorm.
 * User: liseperu
 * Date: 16/08/2016
 * Time: 17:51
 *
 *
 * @AimlTag Lowercase
 * @AimlVersion 1.0,2.0
 * @AimlTagDescription Formats a string to upper upper case
 *
 */

namespace App\Tags;

use App\Classes\LemurLog;
use App\Models\WordTransformation;
use Illuminate\Support\Facades\Log;
use App\Models\Conversation;
use ProgramO\V3\DB;

class Person2Tag extends AimlTag
{
    protected $tagName = "Person2";


    /**
     * Person2Tag Constructor.
     * @param Conversation $conversation
     * @param $attributes
     * which transforms pronouns between first and third person.
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

        $contents = $this->getCurrentResponse(true);
        $words = explode(" ", $contents);
        $preg = $this->getTransformations($words);

        if (empty($preg)) {
            $this->buildResponse($contents);
            return;
        }

        foreach ($words as $word) {
            $change = false;

            foreach ($preg['match'] as $index => $match) {
                $newWord = preg_replace($match, $preg['replace'][$index], $word);

                if ($newWord!=$word) {
                    $change=true;
                    $this->buildResponse($newWord);
                    break;
                }
            }

            if (!$change) {
                $this->buildResponse($word);
            }
        }
    }


    public function getTransformations($words)
    {

        $transformations = WordTransformation::select(['first_person_form','third_person_form'])
            ->whereIn('first_person_form', $words)->orWhereIn('third_person_form', $words)->get();
        $preg = [];

        foreach ($transformations as $transform) {
            $preg['match'][]="/\b".$transform->first_person_form."\b/is";
            $preg['replace'][]=$transform->third_person_form;
            $preg['match'][]="/\b".$transform->third_person_form."\b/is";
            $preg['replace'][]=$transform->first_person_form;
        }

        return $preg;
    }
}
