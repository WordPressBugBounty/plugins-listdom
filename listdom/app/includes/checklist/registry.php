<?php

class LSD_Checklist_Registry
{
    /** @var LSD_Checklist_Check_Interface[] */
    protected array $checks = [];

    public function register(LSD_Checklist_Check_Interface $check): self
    {
        $this->checks[$check->get_id()] = $check;
        return $this;
    }

    /**
     * @return LSD_Checklist_Check_Interface[]
     */
    public function all(): array
    {
        return $this->checks;
    }

    public function get(string $id): ?LSD_Checklist_Check_Interface
    {
        return $this->checks[$id] ?? null;
    }
}
