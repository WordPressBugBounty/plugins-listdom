<?php

class LSD_Actions extends LSD_Base
{
    protected static ?self $instance = null;
    protected ?LSD_Actions_Registry $registry = null;

    public static function instance(): self
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function init(): void
    {
        if (!$this->registry) $this->registry = new LSD_Actions_Registry();
    }

    public function registry(): LSD_Actions_Registry
    {
        $this->init();
        return $this->registry;
    }

    public function execute(string $action_id, array $input = [], array $context_args = []): array
    {
        $context = new LSD_Action_Context($context_args);
        $action = $this->registry()->get($action_id);

        if (!$action)
        {
            return LSD_Action_Result::failure($action_id, 'action_not_found', esc_html__('The requested Listdom action does not exist.', 'listdom'))->to_array();
        }

        LSD_Action_Log::write('requested', $action_id, $context, ['input' => $input]);

        if (!$context->can($action->get_capability()))
        {
            $result = LSD_Action_Result::failure($action_id, 'forbidden', esc_html__('You are not allowed to run this action.', 'listdom'));
            LSD_Action_Log::write('blocked', $action_id, $context, $result->to_array());
            return $result->to_array();
        }

        if ($action->is_mutating() && $context->requires_approval() && !$context->is_approved())
        {
            $result = LSD_Action_Result::failure($action_id, 'approval_required', esc_html__('This action requires explicit approval before it can change site data.', 'listdom'));
            LSD_Action_Log::write('blocked', $action_id, $context, $result->to_array());
            return $result->to_array();
        }

        $validation = $action->validate($input, $context);
        if (!$validation->is_success())
        {
            LSD_Action_Log::write('validation_failed', $action_id, $context, $validation->to_array());
            return $validation->to_array();
        }

        $prepared_input = $validation->get_meta('input', $input);
        if ($action->is_mutating() && $context->is_dry_run())
        {
            $result = $validation->set_meta('dry_run', true);
            LSD_Action_Log::write('dry_run', $action_id, $context, $result->to_array());
            return $result->to_array();
        }

        $result = $action->execute($prepared_input, $context);
        LSD_Action_Log::write($result->is_success() ? 'completed' : 'failed', $action_id, $context, $result->to_array());

        return $result->to_array();
    }
}
