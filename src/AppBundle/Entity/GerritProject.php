<?php
namespace AppBundle\Entity;

class GerritProject
{
    protected $projectId;

    public function getProjectId()
    {
        return $this->projectId;
    }

    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }
}