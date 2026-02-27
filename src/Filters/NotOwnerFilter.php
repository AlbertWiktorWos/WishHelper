<?php

namespace App\Filters;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class NotOwnerFilter extends AbstractFilter
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly Security $security,
        ?NameConverterInterface $nameConverter = null,
        ?array $properties = null,
    ) {
        parent::__construct($managerRegistry, null, $nameConverter, $properties);
    }
    protected function filterProperty(string $property, mixed $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if ('not_owner' !== $property) {
            return;
        }

        if (!$value) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(sprintf('%s.owner != :current_user', $rootAlias))
            ->setParameter('current_user', $this->security->getUser());
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'not_owner' => [
                'type' => Type::BUILTIN_TYPE_BOOL,
                'required' => false,
                'description' => 'Exclude items owned by the currently authenticated user',
                'openapi' => new Parameter(
                    name: 'not_owner',
                    in: 'query',
                    description: 'Exclude items owned by the currently authenticated user',
                    required: false,
                    schema: [
                        'type' => 'boolean',
                        'example' => true,
                    ],
                ),
            ],
        ];
    }
}
