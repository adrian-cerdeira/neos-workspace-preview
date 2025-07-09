<?php
declare(strict_types=1);

namespace Flownative\WorkspacePreview\Controller;

use Flownative\TokenAuthentication\Security\Repository\HashAndRolesRepository;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\Argument;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Security\Authentication\Controller\AbstractAuthenticationController;
use Neos\Neos\Domain\Service\ContentContext;

/**
 *
 */
class HashTokenLoginController extends AbstractAuthenticationController
{
    /**
     * @Flow\Inject
     * @var HashAndRolesRepository
     */
    protected $hashAndRolesRepository;

    /**
     * @param ActionRequest|null $originalRequest
     */
    protected function onAuthenticationSuccess(ActionRequest $originalRequest = null)
    {
        $tokenHash = $this->request->getArgument('_authenticationHashToken');
        $token = $this->hashAndRolesRepository->findByIdentifier($tokenHash);
        if (!$token) {
            return;
        }

        $workspaceName = $token->getSettings()['workspaceName'] ?? '';
        if (empty($workspaceName)) {
            return;
        }

        $possibleNode = $this->getNodeArgumentValue();
        $this->redirectToWorkspace($workspaceName, $possibleNode);
    }

    /**
     * Get a possible node argument from the current request.
     *
     * @return \Neos\ContentRepository\Core\Projection\ContentGraph\Node|null
     */
    protected function getNodeArgumentValue(): ?\Neos\ContentRepository\Core\Projection\ContentGraph\Node
    {
        if (!$this->request->hasArgument('node')) {
            return null;
        }

        $nodeArgument = new Argument('node', \Neos\ContentRepository\Core\Projection\ContentGraph\Node::class);
        $nodeArgument->setValue($this->request->getArgument('node'));
        return $nodeArgument->getValue();
    }

    /**
     * @param string $workspaceName
     * @param \Neos\ContentRepository\Core\Projection\ContentGraph\Node|null $nodeToRedirectTo
     * @throws StopActionException
     */
    protected function redirectToWorkspace(string $workspaceName, \Neos\ContentRepository\Core\Projection\ContentGraph\Node $nodeToRedirectTo = null): void
    {
        /** @var \Neos\Rector\ContentRepository90\Legacy\LegacyContextStub $context */
        $context = new \Neos\Rector\ContentRepository90\Legacy\LegacyContextStub(['workspaceName' => $workspaceName]);

        $nodeInWorkspace = null;
        if ($nodeToRedirectTo instanceof \Neos\ContentRepository\Core\Projection\ContentGraph\Node) {
            $flowQuery = new FlowQuery([$nodeToRedirectTo]);
            $nodeInWorkspace = $flowQuery->context(['workspaceName' => $workspaceName])->get(0);
        }

        if ($nodeInWorkspace === null) {
            // TODO 9.0 migration: !! ContentContext::getCurrentSiteNode() is removed in Neos 9.0. Use Subgraph and traverse up to "Neos.Neos:Site" node.

            $nodeInWorkspace = $context->getCurrentSiteNode();
        }

        $this->redirect('preview', 'Frontend\Node', 'Neos.Neos', ['node' => $nodeInWorkspace]);
    }
}
