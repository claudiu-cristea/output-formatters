<?php
namespace Consolidation\OutputFormatters\Formatters;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

use Consolidation\OutputFormatters\FormatterInterface;
use Consolidation\OutputFormatters\ConfigurationAwareInterface;
use Consolidation\OutputFormatters\Transformations\TableTransformation;
use Consolidation\OutputFormatters\Transformations\PropertyParser;

class TableFormatter implements FormatterInterface, ConfigurationAwareInterface
{
    protected $fieldLabels;
    protected $defaultFields;
    protected $tableStyle;

    /**
     * @inheritdoc
     */
    public function configure($configurationData)
    {
        $configurationData += [
            'field-labels' => [],
            'default-fields' => [],
            'table-style' => 'default',
        ];

        $this->fieldLabels = PropertyParser::parse($configurationData['field-labels']);
        $this->defaultFields = PropertyParser::parse($configurationData['default-fields']);
        $this->tableStyle = $configurationData['table-style'];
    }

    /**
     * @inheritdoc
     */
    public function write($data, $options, OutputInterface $output)
    {
        $options += [
            'table-style' => $this->tableStyle,
            'fields' => $this->defaultFields,
            'include-field-labels' => true,
        ];
        $fieldLabels = $this->selectFieldLabels($options['fields'], $this->fieldLabels, $data);
        $tableTransformer = new TableTransformation($data, $fieldLabels, $options);

        $table = new Table($output);
        $table->setStyle($options['table-style']);
        if ($options['include-field-labels']) {
            $table->setHeaders($tableTransformer->getHeaders());
        }
        $table->setRows($tableTransformer->getData());
        $table->render();
    }

    protected function selectFieldLabels($fields, $fieldLabels, $data)
    {
        $result = [];
        if (empty($fieldLabels)) {
            $fieldLabels = array_combine(array_keys($data[0]), array_keys($data[0]));
        }
        if (empty($fields)) {
            return $fieldLabels;
        }
        foreach ($fields as $field) {
            if (array_key_exists($field, $data[0])) {
                if (array_key_exists($field, $fieldLabels)) {
                    $result[$field] = $fieldLabels[$field];
                }
            }
        }
        return $result;
    }
}