<?php

namespace Ivanstan\SymfonySupport\Services;

use Doctrine\ORM\Mapping\ClassMetadata;
use Ivanstan\SymfonySupport\Attributes\Api;
use Ivanstan\SymfonySupport\Services\Util\ClassUtil;
use Ivanstan\SymfonySupport\Services\Util\DoctrineUtil;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class ApiEntityMetadata
{
    private string $className;

    public function __construct(protected ClassMetadata $meta)
    {
        $this->className = ClassUtil::getClassNameFromFqn($this->getFQN());
    }

    public function getFQN(): string
    {
        return $this->meta->getName();
    }

    public function getFields(): array
    {
        $fields = [];
        foreach ($this->meta->getAssociationMappings() as $name => $association) {
            if (DoctrineUtil::isAssociationCollection($association)) {
                $fields[$name] = [
                    'type' => 'array',
                    'target' => ClassUtil::getClassNameFromFqn($association['targetEntity']),
                ];

                continue;
            }
        }

        foreach ($this->meta->getFieldNames() as $name => $fieldName) {
            $mapping = $this->meta->getFieldMapping($fieldName);

            $fields[$mapping['fieldName']] = [
                'type' => $mapping['type'],
                'nullable' => $mapping['nullable'],
            ];
        }

        return $fields;
    }

    public function getPropertyName(): string
    {
        return str_replace('-', '_', $this->getRouteName());
    }

    public function getRouteName(): string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->normalize($this->className);
    }

    public function getMetaData(): ClassMetadata
    {
        return $this->meta;
    }

    public function getIdentifier(): string
    {
        return $this->meta->identifier[0] ??
            throw new \RuntimeException(\sprintf('Entity %s has no identifier configured.', $this->className));
    }

    public function getAlias(): string
    {
        return $this->meta->table['name'][0] ??
            throw new \RuntimeException(\sprintf('Entity %s has no table name configured.', $this->className));
    }

    public function getName(): string
    {
        return $this->className;
    }

    public function getApiOptions(): ?array
    {
        foreach ($this->meta->reflClass->getAttributes() as $attribute) {
            if ($attribute->getName() === Api::class) {
                return $attribute->getArguments()[0];
            }
        }

        return null;
    }
}
