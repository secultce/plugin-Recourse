<?php
namespace Recourse\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 *
 */

class RecourseFile extends File{

    /**
     * @var \Recourse\Entities\Recourse
     *
     * @ORM\ManyToOne(targetEntity="Recourse\Entities\Recourse", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $owner;

    /**
     * @var \Recourse\Entities\RecourseFile
     *
     * @ORM\ManyToOne(targetEntity="Recourse\Entities\RecourseFile", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $parent;

    public function getUrl(): string
    {
        return \MapasCulturais\App::i()->createUrl('recursos', 'arquivo', [$this->id]);
    }
}
