<?php

class LSD_AI_Models_ClaudeHaiku35 extends LSD_AI_Models_Anthropic implements LSD_AI_Model
{
    public function key(): string
    {
        return LSD_AI_Models::ANTHROPIC_CLAUDE_HAIKU_35;
    }
}
