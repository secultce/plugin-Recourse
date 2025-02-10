<?php

// Url de redirecionamento
/** @var \MapasCulturais\App $app */
$url = $app->createUrl('recursos/opportunities/' . $entity->id);
$this->jsObject['isAdmin'] = $entity->canUser("@control");
$visibledTab = false;

$this->applyTemplateHook('ource--tab', 'before');
$this->applyTemplateHook('opportunity-recourse--tab', 'begin');

$evaluators = $entity->getEvaluationCommittee(false);
$isEvaluator = array_filter($evaluators, function ($evaluator) use ($app) {
    return $evaluator->userId === $app->auth->authenticatedUser->id;
});

if ($entity->canUser('@control') || $isEvaluator):

?>

    <li><a href="#recursos" onclick="redirectRecourse()"><?php \MapasCulturais\i::_e("Recursos"); ?></a></li>

<?php

endif; // fim condição de administrador

$this->applyTemplateHook('opportunity-recourse--tab', 'end');
$this->applyTemplateHook('opportunity-recourse--tab', 'after');

?>

<script>
    function redirectRecourse() {
        window.location.href = MapasCulturais.baseURL + 'recursos/oportunidade/' + MapasCulturais.entity.id
    }
</script>
