<?php 
namespace Recourse\Entities;

use Doctrine\ORM\Mapping as ORM;
use \MapasCulturais\App;
/**
 * Recourse 
 * 
 * @ORM\Table(name="recourse")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class Recourse extends \MapasCulturais\Entity {
    use \MapasCulturais\Traits\EntityRevision;
    //const STATUS_ENABLED = 1; - self:STATUS_ENABLED para deferido
    //const STATUS_DISABLED = -9; - self:STATUS_DISABLED para indeferido
    // const STATUS_DRAFT = 0; - self:STATUS_DRAFT para status inicial
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
     * @ORM\Column(name="recourse_send", type="datetime", nullable=true)
     */
    protected $recourseSend;

    /**
     * @var string
     *
     * @ORM\Column(name="recourse_status", type="string", nullable=false)
     */
    protected $recourseStatus;
    
     /**
     * @var string
     *
     * @ORM\Column(name="recourse_reply", type="text", nullable=true)
     */
    protected $recourseReply;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="recourse_date_reply", type="datetime", nullable=true)
     */
    protected $recourseDateReply;

    /**
     * @var \MapasCulturais\Entities\Registration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Registration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="registration_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $registration;

    /**
     * @var \MapasCulturais\Entities\Opportunity
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Opportunity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $opportunity;

      /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $agent;

    /**
     * @var integer
     *
     * @ORM\Column(name="reply_agent_id", type="integer", nullable=true)
     */
    protected $replyAgentId = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="reply_publish", type="boolean", nullable=true)
     */
    protected $replyPublish = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var Object
     *
     * @param $opportunityId integer
     */
    public static function publishResource($opportunityId) {
        $app = App::i();
        $dql = "UPDATE Recourse\Entities\Recourse r SET r.replyPublish = true WHERE r.opportunity = {$opportunityId}";
        $query = $app->em->createQuery($dql);
        $resource = $query->getResult();
        return $resource;
    }
}