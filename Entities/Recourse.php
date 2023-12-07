<?php 
namespace Recourse\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Item 
 * 
 * @ORM\Table(name="recourse")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class Recourse extends \MapasCulturais\Entity {
   /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="recourse_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="recourse_text", type="text", nullable=false)
     */
    protected $recourseText;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="recourse_send", type="datetime", nullable=false)
     */
    protected $recourseSend;
    
}