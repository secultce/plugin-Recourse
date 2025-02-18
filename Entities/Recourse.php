<?php 
namespace Recourse\Entities;

use Doctrine\ORM\Mapping as ORM;
use \MapasCulturais\App;
use MapasCulturais\Entities\Registration;

/**
 * Recourse 
 * 
 * @ORM\Table(name="recourse")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="Recourse\Repositories\Recourse")
 */
class Recourse extends \MapasCulturais\Entity {
    use \MapasCulturais\Traits\EntityRevision;

    const STATUS_APPROVED = self::STATUS_ENABLED; // 1 - self:STATUS_ENABLED para deferido
    const STATUS_PARTIALLY_APPROVED = Registration::STATUS_WAITLIST; // 8 - Registration::STATUS_WAITLIST para deferido parcialmente
    const STATUS_REJECTED =  self::STATUS_DISABLED; // -9 - self:STATUS_DISABLED para indeferido
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
     * @ORM\OneToMany(targetEntity="Recourse\Entities\RecourseFile", mappedBy="owner", cascade="remove", orphanRemoval=true)
     */
    protected $files = [];

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
    protected $status;
    
     /**
     * @var string
     *
     * @ORM\Column(name="recourse_reply", type="text", nullable=true)
     */
    protected $recourseReply;

    /**
     * @var string
     *
     * @ORM\Column(name="reply_result", type="text", nullable=true)
     */
    protected $replyResult;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="recourse_date_reply", type="datetime", nullable=true)
     */
    protected $recourseDateReply;

    /**
     * @var \MapasCulturais\Entities\Registration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Registration", inversedBy="Recourse\Entities\Recourse", cascade={"persist"})
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
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent")
     * @ORM\JoinColumns({
     *    @ORM\JoinColumn(name="reply_agent_id", referencedColumnName="id", nullable=true)
     * })
     */
    protected $replyAgent = null;

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

    protected function canUserViewPrivateFiles($user): bool {
        return $this->owner->registration->canUser('view');
    }

    public static function publishRecourse($opportunityId): int
    {
        $app = App::i();
        $dql = "UPDATE Recourse\Entities\Recourse r SET r.replyPublish = true WHERE r.opportunity = {$opportunityId}";
        $query = $app->em->createQuery($dql);
        return $query->getResult();
    }

    public function jsonSerialize(): array
    {
        $serialized = parent::jsonSerialize(); // TODO: Change the autogenerated stub

        foreach ($this->files as $file) {
            $serialized['files'][] = ['url' => $file->url, 'name' => $file->name];
        }

        return $serialized;
    }
}
