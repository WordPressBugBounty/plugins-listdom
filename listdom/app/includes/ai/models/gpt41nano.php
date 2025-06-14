<?php

class LSD_AI_Models_GPT41Nano extends LSD_AI_Models_GPT implements LSD_AI_Model
{
    public function key(): string
    {
        return LSD_AI_Models::OPENAI_GPT_41_NANO;
    }
}
