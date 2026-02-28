<?php

namespace App\Filters;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Entity\User;
use App\Entity\WishItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class WishItemTagsAndFilter extends AbstractFilter
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        ?LoggerInterface $logger = null,
        ?array $properties = null,
        ?NameConverterInterface $nameConverter = null,
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    /**
     * Filter by tags related to object (user or wishitem).
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ('tags' !== $property || empty($value) || !is_array($value) || !in_array($resourceClass, [User::class, WishItem::class])) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $subAlias = $queryNameGenerator->generateJoinAlias('tags');

        $manager = $this->managerRegistry->getManagerForClass($resourceClass);
        assert($manager instanceof EntityManagerInterface);
        $subQb = $manager->createQueryBuilder();

        $subQb
            ->select('1')
            ->from($resourceClass, 'w2')
            ->join('w2.tags', $subAlias)
            ->where("w2 = $rootAlias")
            ->andWhere($subQb->expr()->in("$subAlias.name", ':tags'));

        $queryBuilder
            ->andWhere($queryBuilder->expr()->exists($subQb->getDQL()))
            ->setParameter('tags', $value);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'tags' => [
                'type' => Type::BUILTIN_TYPE_ARRAY,
                'property' => 'tags',
                'required' => false,
                'openapi' => new Parameter(
                    name: 'not_owner',
                    in: 'query',
                    description: 'Filter by tags (AND logic)',
                    required: false,
                    schema: [
                        'type' => 'array',
                        'example' => ['test'],
                    ],
                ),
            ],
        ];
    }
}
