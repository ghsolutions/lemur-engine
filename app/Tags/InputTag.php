<?php
/**
 * Created by PhpStorm.
 * User: maczilla
 * Date: 08/04/16
 * Time: 17:06
 *
 * When a random tag is encounted it is assumed that it will contain <li>options</li> inside
 * This class will create a randomly named array upon option
 * store the encounted <li>values</li>
 * and select an item when closed
 *
 *
 *
 */
namespace App\Tags;

use App\Classes\LemurLog;
use App\Classes\LemurStr;
use App\Models\Turn;
use Illuminate\Support\Facades\Log;
use App\Tags\AimlTag;
use App\Models\Conversation;

/**
 * Class Input
 * @package App\Tags
 *
 * <input index=“1”>
 *
 */


class InputTag extends AimlTag
{
    protected $tagName = "Input";

    /**
     * InputTag Constructor.
     * @param Conversation $conversation
     * @param $attributes
     * <input/> <input index="1"/> - the current input sentence
     * <input index="2"/> - the previous input sentence
     * <input index="N"/> - the Nth previous input sentence.
     * e.g. Hello. My name is Bob
     * Hello = <input/>
     * My name is Bob = <input index="2"/>
     */
    public function __construct(Conversation $conversation, $attributes = [])
    {

        parent::__construct($conversation, $attributes);
    }

    /**
     * @return string|void
     */
    public function closeTag()
    {


        //get the index in question and if there is none set it to the default which is 1
        if ($this->hasAttribute('INDEX')) {
            $index = $this->getAttribute('INDEX');
        } else {
            $index = 1;
        }

        //For offset purposes 1=0, 2=1 etc so decrememnt the index by 1 for the offset
        $offset = $index-1;

        //this is a v lazy way of doing this
        $turns = Turn::where('conversation_id', $this->conversation->id)
            ->where('source', 'human')->latest('id')->take(10)->get();

        $inputArray=[];

        foreach ($turns as $turn) {
            $allTurnSentences = explode(".", $turn->input);
            $inputArray = array_merge($inputArray, $allTurnSentences);
        }


        if (!isset($inputArray[$offset])) {
            $input = $this->getUnknownValueStr('input');
        } else {
            $input = trim($inputArray[$offset]);
        }

        $this->buildResponse($input);
    }
}
