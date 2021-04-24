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
use Illuminate\Support\Facades\Log;
use App\Tags\AimlTag;
use App\Models\Conversation;
use ProgramO\V3\DB;

/**
 * Class That
 * @package App\Tags
 *
 * <that index=“1”> = What are you testing? Not me I hope.
 * <that index=“1,1”> = Not me I hope.
 * <that index=“1,2”> = What are you testing?
 * <that index="x,y">
 * the x=1 the response is last response
 * the x=2 the response is second to last response
 *
 * if y=1 the response is after any sentence separator
 * if y=2 the response is before any sentence separator
 *
 */


class JavascriptTag extends AimlTag
{
    protected $tagName = "Javascript";

    /**
     * JavascriptTag Constructor.
     * @param Conversation $conversation
     * @param $attributes
     */
    public function __construct(Conversation $conversation, $attributes = [])
    {

        parent::__construct($conversation, $attributes);
    }


    /**
     * when we close the tag
     * just return send the response all the way up the tag stack
     * all the way up to the template tag
     *
     * @return string
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
        $this->buildResponse('[script]'.$contents.'[/script]');
    }
}
