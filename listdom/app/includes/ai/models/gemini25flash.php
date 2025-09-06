<?php

class LSD_AI_Models_Gemini25Flash extends LSD_AI_Models_Google implements LSD_AI_Model
{
    public function key(): string
    {
        return LSD_AI_Models::GOOGLE_GEMINI_25_FLASH;
    }
}
