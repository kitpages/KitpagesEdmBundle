<?php

namespace Kitpages\EdmBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Doctrine\ORM\EntityRepository;
use Kitpages\EdmBundle\Entity\Node;
use Kitpages\EdmBundle\EdmException;

/**
 * ItemRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NodeRepository extends NestedTreeRepository
{

    /**
     * Get one root node query builder
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    public function getRootNodeByTreeId($treeId)
    {

        return $this->getRootNodesQueryBuilder()
            ->AndWhere(" node.treeId = :treeId")
            ->setParameter("treeId", $treeId);

    }

    public function getFirstDirectoryNodeParentByNode($node)
    {
        $node = $this->_em
            ->createQuery("
                SELECT n
                FROM KitpagesEdmBundle:Node n
                WHERE n.right > :right
                  AND n.left < :left
                  AND n.nodeType = :nodeType
                ORDER BY n.left DESC
              ")
            ->setParameter("right", $node->getRight())
            ->setParameter("left", $node->getLeft())
            ->setParameter("nodeType", Node::NODE_TYPE_DIRECTORY)
            ->setMaxResults(1)
            ->getResult();
        if (count($node) > 0) {
            return $node[0];
        } else {
            throw new EdmException("No parent directory");
        }
    }

    public function getNodeByFileId($fileId)
    {
        $connection = $this->_em->getConnection();


        $stmt  = $connection->executeQuery('
            SELECT n.id
            FROM kit_edm_node n
            WHERE n.file_id = (
                SELECT f2.id
                FROM kit_edm_file f
                INNER JOIN kit_edm_file f2
                ON (f2.original_version_id = f.original_version_id or f2.original_version_id=f.id)
                WHERE f.id = ?
                ORDER BY f2.id DESC LIMIT 1)
            OR n.file_id = ?',
            array($fileId, $fileId),
            array(\PDO::PARAM_INT)
        );
        $nodeId = $stmt->fetch();
        $node = $this->_em->find('KitpagesEdmBundle:Node', $nodeId['id']);

        return $node;
    }

    public function getChildrenDirectNoDisable($node)
    {
        $nodeList = $this->_em
            ->createQuery("
                SELECT n
                FROM KitpagesEdmBundle:Node n
                WHERE n.right < :right
                  AND n.left > :left
                  AND n.parent = :node
                  AND n.status is null
                ORDER BY n.left
              ")
            ->setParameter("right", $node->getRight())
            ->setParameter("left", $node->getLeft())
            ->setParameter("node", $node)
            ->getResult();
        return $nodeList;
    }

}