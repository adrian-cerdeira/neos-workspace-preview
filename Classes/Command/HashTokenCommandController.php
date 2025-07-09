<?php
declare(strict_types=1);

namespace Flownative\WorkspacePreview\Command;

use Flownative\WorkspacePreview\WorkspacePreviewTokenFactory;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 *
 */
class HashTokenCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var WorkspacePreviewTokenFactory
     */
    protected $workspacePreviewTokenFactory;

    /**
     * Create a token for previewing the workspace with the given name/identifier.
     *
     * @param string $workspaceName
     */
    public function createWorkspacePreviewTokenCommand(string $workspaceName): void
    {
        $this->createAndOutputWorkspacePreviewToken($workspaceName);
        $this->persistenceManager->persistAll();
    }

    /**
     * Create preview tokens for all internal and private workspaces (not personal though)
     */
    public function createForAllPossibleWorkspacesCommand(): void
    {
        /** @var \Neos\ContentRepository\Core\SharedModel\Workspace\Workspace $workspace */
        foreach ($this->workspaceRepository->findAll() as $workspace) {
            // TODO 9.0 migration: !! Workspace::isPrivateWorkspace() has been removed in Neos 9.0. Please use the new Workspace permission api instead. See ContentRepositoryAuthorizationService::getWorkspacePermissions()

            // TODO 9.0 migration: !! Workspace::isPrivateWorkspace() has been removed in Neos 9.0. Please use the new Workspace permission api instead. See ContentRepositoryAuthorizationService::getWorkspacePermissions()

            if ($workspace->isPrivateWorkspace() || $workspace->isInternalWorkspace()) {
                // TODO 9.0 migration: Check if you could change your code to work with the WorkspaceName value object instead.

                $this->createAndOutputWorkspacePreviewToken($workspace->workspaceName->value);
            }
        }
        $this->persistenceManager->persistAll();
    }

    /**
     * Creates a token and outputs information.
     *
     * @param string $workspaceName
     * @return void
     */
    private function createAndOutputWorkspacePreviewToken(string $workspaceName): void
    {
        $tokenmetadata = $this->workspacePreviewTokenFactory->create($workspaceName);
        $this->outputLine('Created token for "%s" with hash "%s"', [$workspaceName, $tokenmetadata->getHash()]);
    }
}
