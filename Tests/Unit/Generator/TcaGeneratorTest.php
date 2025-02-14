<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\ContentBlocks\Tests\Unit\Generator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\ContentBlocks\Backend\Preview\PreviewRenderer;
use TYPO3\CMS\ContentBlocks\Definition\Factory\ContentBlockCompiler;
use TYPO3\CMS\ContentBlocks\Definition\Factory\TableDefinitionCollectionFactory;
use TYPO3\CMS\ContentBlocks\Generator\FlexFormGenerator;
use TYPO3\CMS\ContentBlocks\Generator\TcaGenerator;
use TYPO3\CMS\ContentBlocks\Loader\LoadedContentBlock;
use TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry;
use TYPO3\CMS\ContentBlocks\Schema\FieldTypeResolver;
use TYPO3\CMS\ContentBlocks\Schema\SimpleTcaSchemaFactory;
use TYPO3\CMS\ContentBlocks\Tests\Unit\Fixtures\FieldTypeRegistryTestFactory;
use TYPO3\CMS\ContentBlocks\Tests\Unit\Fixtures\NoopLanguageFileRegistry;
use TYPO3\CMS\ContentBlocks\Tests\Unit\Fixtures\TestSystemExtensionAvailability;
use TYPO3\CMS\Core\Cache\Frontend\NullFrontend;
use TYPO3\CMS\Core\Configuration\Event\BeforeTcaOverridesEvent;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class TcaGeneratorTest extends UnitTestCase
{
    public static function checkTcaFieldTypesDataProvider(): iterable
    {
        yield 'Two simple content block create two types and two columns in tt_content table' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_example',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_example',
                        'saveAndClose' => true,
                        'fields' => [
                            [
                                'identifier' => 'bodytext',
                                'type' => 'Textarea',
                                'useExistingField' => true,
                                'enableRichtext' => true,
                            ],
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                                'default' => 'Default value',
                                'placeholder' => 'Placeholder text',
                            ],
                            [
                                'identifier' => 'palette_1',
                                'type' => 'Palette',
                                'fields' => [
                                    [
                                        'identifier' => 'textarea',
                                        'type' => 'Textarea',
                                    ],
                                    [
                                        'type' => 'Linebreak',
                                    ],
                                    [
                                        'identifier' => 'number',
                                        'type' => 'Number',
                                    ],
                                ],
                            ],
                            [
                                'identifier' => 'email',
                                'type' => 'Email',
                            ],
                            [
                                'identifier' => 'check',
                                'type' => 'Checkbox',
                                'items' => [
                                    ['label' => 'Check1'],
                                    ['label' => 'Check2'],
                                ],
                            ],
                            [
                                'identifier' => 'color',
                                'type' => 'Color',
                            ],
                            [
                                'identifier' => 'file',
                                'type' => 'File',
                                'extendedPalette' => 1,
                            ],
                            [
                                'identifier' => 'assets',
                                'useExistingField' => true,
                                'extendedPalette' => 1,
                                'allowed' => 'png',
                            ],
                            [
                                'identifier' => 'pages',
                                'useExistingField' => true,
                                'allowed' => 'tt_content',
                            ],
                            [
                                'identifier' => 'category',
                                'type' => 'Category',
                            ],
                            [
                                'identifier' => 'datetime',
                                'type' => 'DateTime',
                            ],
                            [
                                'identifier' => 'tab_1',
                                'type' => 'Tab',
                            ],
                            [
                                'identifier' => 'select',
                                'type' => 'Select',
                                'renderType' => 'selectSingle',
                                'items' => [
                                    ['value' => ''],
                                    ['label' => 1, 'value' => 'select_1'],
                                    ['label' => 'Select2', 'value' => 'select_2'],
                                ],
                            ],
                            [
                                'identifier' => 'link',
                                'type' => 'Link',
                            ],
                            [
                                'identifier' => 'radio',
                                'type' => 'Radio',
                                'items' => [
                                    ['label' => 'Radio1', 'value' => '1'],
                                    ['label' => 'Radio2', 'value' => '2'],
                                ],
                            ],
                            [
                                'identifier' => 'relation',
                                'type' => 'Relation',
                            ],
                            [
                                'identifier' => 'password',
                                'type' => 'Password',
                            ],
                            [
                                'identifier' => 'uuid',
                                'type' => 'Uuid',
                            ],
                            [
                                'identifier' => 'pass',
                                'type' => 'Pass',
                            ],
                            [
                                'identifier' => 'collection',
                                'type' => 'Collection',
                                'labelField' => 'text2',
                                'fallbackLabelFields' => [
                                    'text',
                                ],
                                'appearance' => [
                                    'useSortable' => false,
                                ],
                                'behaviour' => [
                                    'allowLanguageSynchronization' => true,
                                ],
                                'fields' => [
                                    [
                                        'identifier' => 'text',
                                        'type' => 'Text',
                                    ],
                                    [
                                        'identifier' => 'tab_1',
                                        'type' => 'Tab',
                                    ],
                                    [
                                        'identifier' => 'text2',
                                        'type' => 'Text',
                                    ],
                                    [
                                        'identifier' => 'palette_inline',
                                        'type' => 'Palette',
                                        'fields' => [
                                            [
                                                'identifier' => 'palette_field1',
                                                'type' => 'Text',
                                            ],
                                            [
                                                'type' => 'Linebreak',
                                            ],
                                            [
                                                'identifier' => 'palette_field2',
                                                'type' => 'Text',
                                            ],
                                        ],
                                    ],
                                    [
                                        'identifier' => 'collection2',
                                        'type' => 'Collection',
                                        'fields' => [
                                            [
                                                'identifier' => 'text',
                                                'type' => 'Text',
                                            ],
                                            [
                                                'identifier' => 'text2',
                                                'type' => 'Text',
                                            ],
                                        ],
                                    ],
                                    [
                                        'identifier' => 'collection_mm',
                                        'type' => 'Collection',
                                        'MM' => 'collection_mm_table',
                                        'fields' => [
                                            [
                                                'identifier' => 'text',
                                                'type' => 'Text',
                                            ],
                                            [
                                                'identifier' => 'text2',
                                                'type' => 'Text',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 't3ce/testblock',
                    'extPath' => 'EXT:foo/ContentBlocks/testblock',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_testblock',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_testblock',
                        'fields' => [
                            [
                                'identifier' => 'bodytext',
                                'type' => 'Textarea',
                                'useExistingField' => true,
                            ],
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                                'default' => '',
                                'placeholder' => '',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'ctrl' => [
                        'typeicon_classes' => [
                            't3ce_example' => 'tt_content-t3ce_example',
                            't3ce_testblock' => 'tt_content-t3ce_testblock',
                        ],
                        'searchFields' => 'header,header_link,subheader,bodytext,pi_flexform,t3ce_testblock_text,t3ce_example_text,t3ce_example_textarea,t3ce_example_email,t3ce_example_color,t3ce_example_link,t3ce_example_uuid',
                    ],
                    'types' => [
                        't3ce_example' => [
                            'showitem' => 'bodytext,t3ce_example_text,--palette--;;t3ce_example_palette_1,t3ce_example_email,t3ce_example_check,t3ce_example_color,t3ce_example_file,assets,pages,t3ce_example_category,t3ce_example_datetime,--div--;LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:tabs.tab_1,t3ce_example_select,t3ce_example_link,t3ce_example_radio,t3ce_example_relation,t3ce_example_password,t3ce_example_uuid,t3ce_example_collection',
                            'previewRenderer' => PreviewRenderer::class,
                            'creationOptions' => [
                                'saveAndClose' => true,
                            ],
                            'columnsOverrides' => [
                                'bodytext' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:bodytext.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:bodytext.description',
                                    'config' => [
                                        'enableRichtext' => true,
                                    ],
                                ],
                                't3ce_example_text' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text.description',
                                    'config' => [
                                        'default' => 'Default value',
                                        'placeholder' => 'Placeholder text',
                                    ],
                                ],
                                't3ce_example_textarea' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:textarea.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:textarea.description',
                                ],
                                't3ce_example_number' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:number.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:number.description',
                                ],
                                't3ce_example_email' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:email.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:email.description',
                                ],
                                't3ce_example_check' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:check.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:check.description',
                                ],
                                't3ce_example_color' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:color.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:color.description',
                                ],
                                't3ce_example_file' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:file.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:file.description',
                                ],
                                't3ce_example_category' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:category.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:category.description',
                                ],
                                't3ce_example_datetime' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:datetime.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:datetime.description',
                                ],
                                't3ce_example_select' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:select.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:select.description',
                                    'config' => [
                                        'renderType' => 'selectSingle',
                                    ],
                                ],
                                't3ce_example_link' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:link.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:link.description',
                                ],
                                't3ce_example_radio' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:radio.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:radio.description',
                                ],
                                't3ce_example_relation' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:relation.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:relation.description',
                                ],
                                't3ce_example_collection' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.description',
                                    'config' => [
                                        'appearance' => [
                                            'useSortable' => false,
                                        ],
                                    ],
                                ],
                                'assets' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:assets.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:assets.description',
                                    'config' => [
                                        'allowed' => 'png',
                                    ],
                                ],
                                'pages' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:pages.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:pages.description',
                                ],
                                't3ce_example_password' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:password.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:password.description',
                                ],
                                't3ce_example_uuid' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:uuid.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:uuid.description',
                                ],
                                't3ce_example_pass' => [
                                ],
                            ],
                        ],
                        't3ce_testblock' => [
                            'showitem' => 'bodytext,t3ce_testblock_text',
                            'previewRenderer' => PreviewRenderer::class,
                            'columnsOverrides' => [
                                't3ce_testblock_text' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/testblock/language/labels.xlf:text.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/testblock/language/labels.xlf:text.description',
                                ],
                                'bodytext' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/testblock/language/labels.xlf:bodytext.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/testblock/language/labels.xlf:bodytext.description',
                                ],
                            ],
                        ],
                    ],
                    'columns' => [
                        't3ce_example_text' => [
                            'label' => 'text',
                            'config' => [
                                'type' => 'input',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_textarea' => [
                            'label' => 'textarea',
                            'config' => [
                                'type' => 'text',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_number' => [
                            'label' => 'number',
                            'config' => [
                                'type' => 'number',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_email' => [
                            'label' => 'email',
                            'config' => [
                                'type' => 'email',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_check' => [
                            'label' => 'check',
                            'config' => [
                                'type' => 'check',
                                'items' => [
                                    [
                                        'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:check.items.0.label',
                                    ],
                                    [
                                        'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:check.items.1.label',
                                    ],
                                ],
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_color' => [
                            'label' => 'color',
                            'config' => [
                                'type' => 'color',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_file' => [
                            'label' => 'file',
                            'config' => [
                                'type' => 'file',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_category' => [
                            'label' => 'category',
                            'config' => [
                                'type' => 'category',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_datetime' => [
                            'label' => 'datetime',
                            'config' => [
                                'type' => 'datetime',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_select' => [
                            'label' => 'select',
                            'config' => [
                                'type' => 'select',
                                'items' => [
                                    [
                                        'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:select.items.label',
                                        'value' => '',
                                    ],
                                    [
                                        'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:select.items.select_1.label',
                                        'value' => 'select_1',
                                    ],
                                    [
                                        'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:select.items.select_2.label',
                                        'value' => 'select_2',
                                    ],
                                ],
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_link' => [
                            'label' => 'link',
                            'config' => [
                                'type' => 'link',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_radio' => [
                            'label' => 'radio',
                            'config' => [
                                'type' => 'radio',
                                'items' => [
                                    [
                                        'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:radio.items.1.label',
                                        'value' => '1',
                                    ],
                                    [
                                        'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:radio.items.2.label',
                                        'value' => '2',
                                    ],
                                ],
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_relation' => [
                            'label' => 'relation',
                            'config' => [
                                'type' => 'group',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_collection' => [
                            'label' => 'collection',
                            'config' => [
                                'type' => 'inline',
                                'foreign_table' => 't3ce_example_collection',
                                'foreign_field' => 'foreign_table_parent_uid',
                                'behaviour' => [
                                    'allowLanguageSynchronization' => true,
                                ],
                            ],
                            'exclude' => true,
                        ],
                        't3ce_testblock_text' => [
                            'label' => 'text',
                            'config' => [
                                'type' => 'input',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_password' => [
                            'label' => 'password',
                            'config' => [
                                'type' => 'password',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_uuid' => [
                            'label' => 'uuid',
                            'config' => [
                                'type' => 'uuid',
                            ],
                            'exclude' => true,
                        ],
                        't3ce_example_pass' => [
                            'label' => 'pass',
                            'config' => [
                                'type' => 'passthrough',
                            ],
                        ],
                    ],
                    'palettes' => [
                        't3ce_example_palette_1' => [
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:palettes.palette_1.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:palettes.palette_1.description',
                            'showitem' => 't3ce_example_textarea,--linebreak--,t3ce_example_number',
                        ],
                    ],
                ],
                't3ce_example_collection' => [
                    'ctrl' => [
                        'title' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.label',
                        'label' => 'text2',
                        'label_alt' => 'text',
                        'sortby' => 'sorting',
                        'tstamp' => 'tstamp',
                        'crdate' => 'crdate',
                        'delete' => 'deleted',
                        'editlock' => 'editlock',
                        'versioningWS' => true,
                        'hideTable' => true,
                        'translationSource' => 'l10n_source',
                        'transOrigDiffSourceField' => 'l10n_diffsource',
                        'languageField' => 'sys_language_uid',
                        'enablecolumns' => [
                            'disabled' => 'hidden',
                            'starttime' => 'starttime',
                            'endtime' => 'endtime',
                            'fe_group' => 'fe_group',
                        ],
                        'typeicon_classes' => [
                            'default' => 't3ce_example_collection-1',
                        ],
                        'searchFields' => 'text,text2,palette_field1,palette_field2',
                        'security' => [
                            'ignorePageTypeRestriction' => true,
                        ],
                        'previewRenderer' => PreviewRenderer::class,
                    ],
                    'types' => [
                        '1' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,text,--div--;LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.tabs.tab_1,text2,--palette--;;palette_inline,collection2,collection_mm,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access',
                        ],
                    ],
                    'palettes' => [
                        'language' => [
                            'showitem' => 'sys_language_uid,l10n_parent',
                        ],
                        'hidden' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
                            'showitem' => 'hidden',
                        ],
                        'access' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,--linebreak--,fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,--linebreak--,editlock',
                        ],
                        'palette_inline' => [
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.palettes.palette_inline.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.palettes.palette_inline.description',
                            'showitem' => 'palette_field1,--linebreak--,palette_field2',
                        ],
                    ],
                    'columns' => [
                        'foreign_table_parent_uid' => [
                            'config' => [
                                'type' => 'passthrough',
                            ],
                        ],
                        'text' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.text.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.text.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        'text2' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.text2.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.text2.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        'palette_field1' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.palette_field1.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.palette_field1.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        'palette_field2' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.palette_field2.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.palette_field2.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        'collection2' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection2.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection2.description',
                            'config' => [
                                'type' => 'inline',
                                'foreign_table' => 'collection2',
                                'foreign_field' => 'foreign_table_parent_uid',
                            ],
                        ],
                        'collection_mm' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection_mm.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection_mm.description',
                            'config' => [
                                'type' => 'inline',
                                'foreign_table' => 'collection_mm',
                                'MM' => 'collection_mm_table',
                            ],
                        ],
                    ],
                ],
                'collection2' => [
                    'ctrl' => [
                        'title' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection2.label',
                        'label' => 'text',
                        'sortby' => 'sorting',
                        'tstamp' => 'tstamp',
                        'crdate' => 'crdate',
                        'delete' => 'deleted',
                        'editlock' => 'editlock',
                        'versioningWS' => true,
                        'hideTable' => true,
                        'translationSource' => 'l10n_source',
                        'transOrigDiffSourceField' => 'l10n_diffsource',
                        'languageField' => 'sys_language_uid',
                        'enablecolumns' => [
                            'disabled' => 'hidden',
                            'starttime' => 'starttime',
                            'endtime' => 'endtime',
                            'fe_group' => 'fe_group',
                        ],
                        'typeicon_classes' => [
                            'default' => 'collection2-1',
                        ],
                        'searchFields' => 'text,text2',
                        'security' => [
                            'ignorePageTypeRestriction' => true,
                        ],
                        'previewRenderer' => PreviewRenderer::class,
                    ],
                    'types' => [
                        '1' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,text,text2,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access',
                        ],
                    ],
                    'palettes' => [
                        'language' => [
                            'showitem' => 'sys_language_uid,l10n_parent',
                        ],
                        'hidden' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
                            'showitem' => 'hidden',
                        ],
                        'access' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,--linebreak--,fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,--linebreak--,editlock',
                        ],
                    ],
                    'columns' => [
                        'foreign_table_parent_uid' => [
                            'config' => [
                                'type' => 'passthrough',
                            ],
                        ],
                        'text' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection2.text.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection2.text.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        'text2' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection2.text2.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection2.text2.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                    ],
                ],
                'collection_mm' => [
                    'ctrl' => [
                        'title' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection_mm.label',
                        'label' => 'text',
                        'tstamp' => 'tstamp',
                        'crdate' => 'crdate',
                        'delete' => 'deleted',
                        'editlock' => 'editlock',
                        'versioningWS' => true,
                        'hideTable' => true,
                        'translationSource' => 'l10n_source',
                        'transOrigDiffSourceField' => 'l10n_diffsource',
                        'languageField' => 'sys_language_uid',
                        'enablecolumns' => [
                            'disabled' => 'hidden',
                            'starttime' => 'starttime',
                            'endtime' => 'endtime',
                            'fe_group' => 'fe_group',
                        ],
                        'typeicon_classes' => [
                            'default' => 'collection_mm-1',
                        ],
                        'searchFields' => 'text,text2',
                        'security' => [
                            'ignorePageTypeRestriction' => true,
                        ],
                        'previewRenderer' => PreviewRenderer::class,
                    ],
                    'types' => [
                        '1' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,text,text2,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access',
                        ],
                    ],
                    'palettes' => [
                        'language' => [
                            'showitem' => 'sys_language_uid,l10n_parent',
                        ],
                        'hidden' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
                            'showitem' => 'hidden',
                        ],
                        'access' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,--linebreak--,fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,--linebreak--,editlock',
                        ],
                    ],
                    'columns' => [
                        'text' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection_mm.text.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection_mm.text.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        'text2' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection_mm.text2.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.collection_mm.text2.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'First general tab can be overridden' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_example',

                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_example',
                        'fields' => [
                            [
                                'identifier' => 'tab_1',
                                'type' => 'Tab',
                            ],
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'ctrl' => [
                        'typeicon_classes' => [
                            't3ce_example' => 'tt_content-t3ce_example',
                        ],
                        'searchFields' => 'header,header_link,subheader,bodytext,pi_flexform,t3ce_example_text',
                    ],
                    'types' => [
                        't3ce_example' => [
                            'showitem' => '--div--;LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:tabs.tab_1,t3ce_example_text',
                            'previewRenderer' => PreviewRenderer::class,
                            'columnsOverrides' => [
                                't3ce_example_text' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text.description',
                                ],
                            ],
                        ],
                    ],
                    'columns' => [
                        't3ce_example_text' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'exclude' => true,
                            'label' => 'text',
                        ],
                    ],
                ],
            ],
        ];

        yield 'First general tab overridden, no custom fields' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_example',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_example',
                        'fields' => [
                            [
                                'identifier' => 'tab_1',
                                'type' => 'Tab',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'ctrl' => [
                        'typeicon_classes' => [
                            't3ce_example' => 'tt_content-t3ce_example',
                        ],
                        'searchFields' => 'header,header_link,subheader,bodytext,pi_flexform',
                    ],
                    'types' => [
                        't3ce_example' => [
                            'showitem' => '--div--;LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:tabs.tab_1',
                            'previewRenderer' => PreviewRenderer::class,
                        ],
                    ],
                ],
            ],
        ];

        yield 'Content Block creating a new custom root table (not tt_content, generic content type)' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/RecordTypes/example',
                    'icon' => [
                        'iconPath' => 'EXT:foo/ContentBlocks/RecordTypes/example/Assets/icon.svg',
                        'iconProvider' => SvgIconProvider::class,
                        'iconIdentifier' => 'foobar-1',
                    ],
                    'yaml' => [
                        'table' => 'foobar',
                        'fields' => [
                            [
                                'identifier' => 'number',
                                'type' => 'Number',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'foobar' => [
                    'ctrl' => [
                        'title' => 'LLL:EXT:foo/ContentBlocks/RecordTypes/example/language/labels.xlf:title',
                        'label' => 't3ce_example_number',
                        'sortby' => 'sorting',
                        'tstamp' => 'tstamp',
                        'crdate' => 'crdate',
                        'delete' => 'deleted',
                        'editlock' => 'editlock',
                        'versioningWS' => true,
                        'hideTable' => false,
                        'translationSource' => 'l10n_source',
                        'transOrigDiffSourceField' => 'l10n_diffsource',
                        'languageField' => 'sys_language_uid',
                        'enablecolumns' => [
                            'disabled' => 'hidden',
                            'starttime' => 'starttime',
                            'endtime' => 'endtime',
                            'fe_group' => 'fe_group',
                        ],
                        'typeicon_classes' => [
                            'default' => 'foobar-1',
                        ],
                        'searchFields' => '',
                        'previewRenderer' => PreviewRenderer::class,
                    ],
                    'types' => [
                        '1' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,t3ce_example_number,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access',
                        ],
                    ],
                    'palettes' => [
                        'language' => [
                            'showitem' => 'sys_language_uid,l10n_parent',
                        ],
                        'hidden' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
                            'showitem' => 'hidden',
                        ],
                        'access' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,--linebreak--,fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,--linebreak--,editlock',
                        ],
                    ],
                    'columns' => [
                        't3ce_example_number' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/RecordTypes/example/language/labels.xlf:number.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/RecordTypes/example/language/labels.xlf:number.description',
                            'config' => [
                                'type' => 'number',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Content Block creating a new custom root table with TYPO3 specific features disabled / enabled' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'foobar-1',
                    ],
                    'yaml' => [
                        'table' => 'foobar',
                        'labelField' => [
                            'text',
                            'text2',
                        ],
                        'languageAware' => false,
                        'workspaceAware' => false,
                        'restriction' => [
                            'disabled' => false,
                            'startTime' => false,
                        ],
                        'editLocking' => false,
                        'softDelete' => false,
                        'trackCreationDate' => false,
                        'trackUpdateDate' => false,
                        'trackAncestorReference' => false,
                        'sortField' => 'text',
                        'internalDescription' => true,
                        'rootLevelType' => 'onlyOnRootLevel',
                        'security' => [
                            'ignoreWebMountRestriction' => true,
                            'ignoreRootLevelRestriction' => true,
                            'ignorePageTypeRestriction' => true,
                        ],
                        'adminOnly' => true,
                        'readOnly' => true,
                        'hideAtCopy' => true,
                        'appendLabelAtCopy' => 'banana',
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'text2',
                                'type' => 'Text',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'foobar' => [
                    'ctrl' => [
                        'title' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:title',
                        'label' => 't3ce_example_text',
                        'label_alt' => 't3ce_example_text2',
                        'label_alt_force' => true,
                        'hideTable' => false,
                        'enablecolumns' => [
                            'endtime' => 'endtime',
                            'fe_group' => 'fe_group',
                        ],
                        'typeicon_classes' => [
                            'default' => 'foobar-1',
                        ],
                        'rootLevel' => 1,
                        'security' => [
                            'ignoreWebMountRestriction' => true,
                            'ignoreRootLevelRestriction' => true,
                            'ignorePageTypeRestriction' => true,
                        ],
                        'adminOnly' => true,
                        'readOnly' => true,
                        'prependAtCopy' => 'banana',
                        'default_sortby' => 't3ce_example_text',
                        'descriptionColumn' => 'internal_description',
                        'searchFields' => 't3ce_example_text,t3ce_example_text2',
                        'previewRenderer' => PreviewRenderer::class,
                    ],
                    'types' => [
                        '1' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,t3ce_example_text,t3ce_example_text2,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,internal_description',
                        ],
                    ],
                    'palettes' => [
                        'access' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                            'showitem' => 'endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,--linebreak--,fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel',
                        ],
                    ],
                    'columns' => [
                        't3ce_example_text' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        't3ce_example_text2' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text2.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text2.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Content Block creating a new custom root table with TYPO3 specific features disabled / enabled 2' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'foobar-1',
                    ],
                    'yaml' => [
                        'table' => 'foobar',
                        'labelField' => [
                            'text',
                        ],
                        'fallbackLabelFields' => [
                            'text2',
                        ],
                        'languageAware' => false,
                        'workspaceAware' => false,
                        'restriction' => [
                            'endTime' => false,
                            'editLocking' => false,
                        ],
                        'editLocking' => false,
                        'softDelete' => false,
                        'sortable' => false,
                        'trackCreationDate' => false,
                        'trackUpdateDate' => false,
                        'trackAncestorReference' => false,
                        'sortField' => [
                            [
                                'identifier' => 'text',
                                'order' => 'desc',
                            ],
                            [
                                'identifier' => 'text2',
                                'order' => 'asc',
                            ],
                        ],
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'text2',
                                'type' => 'Text',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'foobar' => [
                    'ctrl' => [
                        'title' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:title',
                        'label' => 't3ce_example_text',
                        'label_alt' => 't3ce_example_text2',
                        'default_sortby' => 't3ce_example_text DESC,t3ce_example_text2 ASC',
                        'hideTable' => false,
                        'enablecolumns' => [
                            'starttime' => 'starttime',
                            'disabled' => 'hidden',
                            'fe_group' => 'fe_group',
                        ],
                        'typeicon_classes' => [
                            'default' => 'foobar-1',
                        ],
                        'searchFields' => 't3ce_example_text,t3ce_example_text2',
                        'previewRenderer' => PreviewRenderer::class,
                    ],
                    'types' => [
                        '1' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,t3ce_example_text,t3ce_example_text2,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access',
                        ],
                    ],
                    'palettes' => [
                        'hidden' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
                            'showitem' => 'hidden',
                        ],
                        'access' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,--linebreak--,fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel',
                        ],
                    ],
                    'columns' => [
                        't3ce_example_text' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        't3ce_example_text2' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text2.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text2.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Content Block creating a new custom root table with typeField defined' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/RecordTypes/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'foobar-example',
                    ],
                    'yaml' => [
                        'table' => 'foobar',
                        'priority' => 1,
                        'typeField' => 'type',
                        'typeName' => 'example',
                        'prefixFields' => false,
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 't3ce/example2',
                    'extPath' => 'EXT:foo/ContentBlocks/RecordTypes/example2',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'foobar-example2',
                    ],
                    'yaml' => [
                        'table' => 'foobar',
                        'typeName' => 'example2',
                        'prefixFields' => false,
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Text',
                            ],
                            [
                                'identifier' => 'text2',
                                'type' => 'Textarea',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'foobar' => [
                    'ctrl' => [
                        'title' => 'LLL:EXT:foo/ContentBlocks/RecordTypes/example/language/labels.xlf:title',
                        'label' => 'text',
                        'sortby' => 'sorting',
                        'tstamp' => 'tstamp',
                        'crdate' => 'crdate',
                        'delete' => 'deleted',
                        'editlock' => 'editlock',
                        'versioningWS' => true,
                        'hideTable' => false,
                        'translationSource' => 'l10n_source',
                        'transOrigDiffSourceField' => 'l10n_diffsource',
                        'languageField' => 'sys_language_uid',
                        'enablecolumns' => [
                            'disabled' => 'hidden',
                            'starttime' => 'starttime',
                            'endtime' => 'endtime',
                            'fe_group' => 'fe_group',
                        ],
                        'type' => 'type',
                        'typeicon_column' => 'type',
                        'typeicon_classes' => [
                            'example' => 'foobar-example',
                            'example2' => 'foobar-example2',
                            'default' => 'foobar-example',
                        ],
                        'searchFields' => 'text,text2',
                        'previewRenderer' => PreviewRenderer::class,
                    ],
                    'types' => [
                        'example' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,type,text,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access',
                            'columnsOverrides' => [
                                'text' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/RecordTypes/example/language/labels.xlf:text.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/RecordTypes/example/language/labels.xlf:text.description',
                                ],
                            ],
                        ],
                        'example2' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,type,text,text2,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access',
                            'columnsOverrides' => [
                                'text' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/RecordTypes/example2/language/labels.xlf:text.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/RecordTypes/example2/language/labels.xlf:text.description',
                                ],
                                'text2' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/RecordTypes/example2/language/labels.xlf:text2.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/RecordTypes/example2/language/labels.xlf:text2.description',
                                ],
                            ],
                        ],
                    ],
                    'palettes' => [
                        'language' => [
                            'showitem' => 'sys_language_uid,l10n_parent',
                        ],
                        'hidden' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
                            'showitem' => 'hidden',
                        ],
                        'access' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,--linebreak--,fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,--linebreak--,editlock',
                        ],
                    ],
                    'columns' => [
                        'text' => [
                            'label' => 'text',
                            'exclude' => true,
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        'text2' => [
                            'label' => 'text2',
                            'exclude' => true,
                            'config' => [
                                'type' => 'text',
                            ],
                        ],
                        'type' => [
                            'exclude' => true,
                            'config' => [
                                'type' => 'select',
                                'renderType' => 'selectSingle',
                                'default' => 'example',
                                'items' => [],
                            ],
                            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.type',
                        ],
                    ],
                ],
            ],
        ];

        yield 'prefixing can be disabled globally' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_example',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_example',
                        'prefixFields' => false,
                        'fields' => [
                            [
                                'identifier' => 'text',
                                'type' => 'Textarea',
                            ],
                            [
                                'identifier' => 'palette',
                                'type' => 'Palette',
                                'fields' => [
                                    [
                                        'identifier' => 'color',
                                        'type' => 'Color',
                                    ],
                                ],
                            ],
                            [
                                'identifier' => 'collection',
                                'type' => 'Collection',
                                'fields' => [
                                    [
                                        'identifier' => 'text',
                                        'type' => 'Text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'ctrl' => [
                        'typeicon_classes' => [
                            't3ce_example' => 'tt_content-t3ce_example',
                        ],
                        'searchFields' => 'header,header_link,subheader,bodytext,pi_flexform,text,color',
                    ],
                    'types' => [
                        't3ce_example' => [
                            'showitem' => 'text,--palette--;;palette,collection',
                            'previewRenderer' => PreviewRenderer::class,
                            'columnsOverrides' => [
                                'text' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:text.description',
                                ],
                                'color' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:color.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:color.description',
                                ],
                                'collection' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.description',
                                    'config' => [
                                        'appearance' => [
                                            'useSortable' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'columns' => [
                        'text' => [
                            'label' => 'text',
                            'config' => [
                                'type' => 'text',
                            ],
                            'exclude' => true,
                        ],
                        'collection' => [
                            'label' => 'collection',
                            'config' => [
                                'type' => 'inline',
                                'foreign_table' => 'collection',
                                'foreign_field' => 'foreign_table_parent_uid',
                            ],
                            'exclude' => true,
                        ],
                        'color' => [
                            'label' => 'color',
                            'config' => [
                                'type' => 'color',
                            ],
                            'exclude' => true,
                        ],
                    ],
                    'palettes' => [
                        'palette' => [
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:palettes.palette.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:palettes.palette.description',
                            'showitem' => 'color',
                        ],
                    ],
                ],
                'collection' => [
                    'ctrl' => [
                        'title' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.label',
                        'label' => 'text',
                        'sortby' => 'sorting',
                        'tstamp' => 'tstamp',
                        'crdate' => 'crdate',
                        'delete' => 'deleted',
                        'editlock' => 'editlock',
                        'versioningWS' => true,
                        'hideTable' => true,
                        'translationSource' => 'l10n_source',
                        'transOrigDiffSourceField' => 'l10n_diffsource',
                        'languageField' => 'sys_language_uid',
                        'enablecolumns' => [
                            'disabled' => 'hidden',
                            'starttime' => 'starttime',
                            'endtime' => 'endtime',
                            'fe_group' => 'fe_group',
                        ],
                        'typeicon_classes' => [
                            'default' => 'collection-1',
                        ],
                        'searchFields' => 'text',
                        'previewRenderer' => PreviewRenderer::class,
                        'security' => [
                            'ignorePageTypeRestriction' => true,
                        ],
                    ],
                    'types' => [
                        '1' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,text,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access',
                        ],
                    ],
                    'palettes' => [
                        'language' => [
                            'showitem' => 'sys_language_uid,l10n_parent',
                        ],
                        'hidden' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
                            'showitem' => 'hidden',
                        ],
                        'access' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,--linebreak--,fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,--linebreak--,editlock',
                        ],
                    ],
                    'columns' => [
                        'text' => [
                            'exclude' => true,
                            'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.text.label',
                            'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:collection.text.description',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        'foreign_table_parent_uid' => [
                            'config' => [
                                'type' => 'passthrough',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('checkTcaFieldTypesDataProvider')]
    #[Test]
    public function checkTcaFieldTypes(array $contentBlocks, array $expected): void
    {
        $baseTca['tt_content']['ctrl']['type'] = 'CType';
        $baseTca['tt_content']['columns']['bodytext'] = [
            'label' => 'Core bodytext field',
            'config' => [
                'type' => 'text',
            ],
        ];
        $baseTca['tt_content']['columns']['assets'] = [
            'label' => 'Core assets field',
            'config' => [
                'type' => 'file',
            ],
        ];
        $baseTca['tt_content']['columns']['pages'] = [
            'label' => 'Core pages field',
            'config' => [
                'type' => 'group',
                'allowed' => 'pages',
            ],
        ];
        $baseTca['tt_content']['ctrl']['searchFields'] = 'header,header_link,subheader,bodytext,pi_flexform';

        $fieldTypeRegistry = FieldTypeRegistryTestFactory::create();
        $fieldTypeResolver = new FieldTypeResolver($fieldTypeRegistry);
        $simpleTcaSchemaFactory = new SimpleTcaSchemaFactory($fieldTypeResolver);
        $simpleTcaSchemaFactory->initialize($baseTca);
        $contentBlocks = array_map(fn(array $contentBlock) => LoadedContentBlock::fromArray($contentBlock), $contentBlocks);
        $contentBlockRegistry = new ContentBlockRegistry();
        foreach ($contentBlocks as $contentBlock) {
            $contentBlockRegistry->register($contentBlock);
        }
        $contentBlockCompiler = new ContentBlockCompiler();
        $tableDefinitionCollection = (new TableDefinitionCollectionFactory(new NullFrontend('test'), $contentBlockCompiler))
            ->createUncached($contentBlockRegistry, $fieldTypeRegistry, $simpleTcaSchemaFactory);
        $systemExtensionAvailability = new TestSystemExtensionAvailability();
        $systemExtensionAvailability->addAvailableExtension('workspaces');
        $languageFileRegistry = new NoopLanguageFileRegistry();
        $flexFormGenerator = new FlexFormGenerator($languageFileRegistry);
        $tcaGenerator = new TcaGenerator(
            $tableDefinitionCollection,
            $simpleTcaSchemaFactory,
            $languageFileRegistry,
            $systemExtensionAvailability,
            $flexFormGenerator,
        );
        $tca = $tcaGenerator->generate($baseTca);

        self::assertEquals($expected, $tca);
    }

    public static function pageTypesGenerateCorrectTcaDataProvider(): iterable
    {
        yield 'simple custom page type is added' => [
            'contentBlocks' => [
                [
                    'name' => 'content-blocks/custom-page-type',
                    'extPath' => 'EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'pages-1700156757',
                    ],
                    'iconHideInMenu' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'pages-1700156757-hide-in-menu',
                    ],
                    'yaml' => [
                        'table' => 'pages',
                        'typeField' => 'doktype',
                        'typeName' => 1700156757,
                    ],
                ],
            ],
            'seoExtensionLoaded' => false,
            'expected' => [
                'pages' => [
                    'ctrl' => [
                        'typeicon_classes' => [
                            '1700156757' => 'pages-1700156757',
                        ],
                        'searchFields' => 'title,slug,nav_title',
                    ],
                    'types' => [
                        '1700156757' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;;standard,--palette--;;content_blocks_titleonly,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.metadata,--palette--;;metatags,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.appearance,--palette--;;backend_layout,--palette--;;replace,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.behaviour,--palette--;;links,--palette--;;caching,--palette--;;miscellaneous,--palette--;;module,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.resources,--palette--;;config,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,--palette--;;visibility,--palette--;;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription',
                            'columnsOverrides' => [
                                'title' => [
                                    'label' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:title.label',
                                    'description' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:title.description',
                                ],
                                'slug' => [
                                    'label' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:slug.label',
                                    'description' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:slug.description',
                                ],
                                'nav_title' => [
                                    'label' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:nav_title.label',
                                    'description' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:nav_title.description',
                                ],
                            ],
                        ],
                    ],
                    'columns' => [],
                    'palettes' => [
                        'content_blocks_titleonly' => [
                            'showitem' => 'title,--linebreak--,slug,--linebreak--,nav_title',
                            'label' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:palettes.content_blocks_titleonly.label',
                            'description' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:palettes.content_blocks_titleonly.description',
                        ],
                    ],
                ],
            ],
        ];

        yield 'seo tab is added if seo extension is loaded' => [
            'contentBlocks' => [
                [
                    'name' => 'content-blocks/custom-page-type',
                    'extPath' => 'EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'pages-1700156757',
                    ],
                    'yaml' => [
                        'table' => 'pages',
                        'typeField' => 'doktype',
                        'typeName' => 1700156757,
                    ],
                ],
            ],
            'seoExtensionLoaded' => true,
            'expected' => [
                'pages' => [
                    'ctrl' => [
                        'typeicon_classes' => [
                            '1700156757' => 'pages-1700156757',
                        ],
                        'searchFields' => 'title,slug,nav_title',
                    ],
                    'types' => [
                        '1700156757' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;;standard,--palette--;;content_blocks_titleonly,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.metadata,--palette--;;metatags,--div--;LLL:EXT:seo/Resources/Private/Language/locallang_tca.xlf:pages.tabs.seo,--palette--;;seo,--palette--;;robots,--palette--;;canonical,--palette--;;sitemap,--div--;LLL:EXT:seo/Resources/Private/Language/locallang_tca.xlf:pages.tabs.socialmedia,--palette--;;opengraph,--palette--;;twittercards,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.appearance,--palette--;;backend_layout,--palette--;;replace,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.behaviour,--palette--;;links,--palette--;;caching,--palette--;;miscellaneous,--palette--;;module,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.resources,--palette--;;config,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,--palette--;;visibility,--palette--;;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription',
                            'columnsOverrides' => [
                                'title' => [
                                    'label' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:title.label',
                                    'description' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:title.description',
                                ],
                                'slug' => [
                                    'label' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:slug.label',
                                    'description' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:slug.description',
                                ],
                                'nav_title' => [
                                    'label' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:nav_title.label',
                                    'description' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:nav_title.description',
                                ],
                            ],
                        ],
                    ],
                    'columns' => [],
                    'palettes' => [
                        'content_blocks_titleonly' => [
                            'showitem' => 'title,--linebreak--,slug,--linebreak--,nav_title',
                            'label' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:palettes.content_blocks_titleonly.label',
                            'description' => 'LLL:EXT:my_sitepackage/ContentBlocks/PageTypes/custom-page-type/language/labels.xlf:palettes.content_blocks_titleonly.description',
                        ],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('pageTypesGenerateCorrectTcaDataProvider')]
    #[Test]
    public function pageTypesGenerateCorrectTca(array $contentBlocks, bool $seoExtensionLoaded, array $expected): void
    {
        $baseTca['pages']['ctrl']['type'] = 'doktype';
        $baseTca['pages']['ctrl']['label'] = 'title';
        $baseTca['pages']['columns'] = [
            'title' => [
                'config' => [
                    'type' => 'input',
                ],
            ],
            'slug' => [
                'config' => [
                    'type' => 'slug',
                ],
            ],
            'nav_title' => [
                'config' => [
                    'type' => 'input',
                ],
            ],
        ];

        $fieldTypeRegistry = FieldTypeRegistryTestFactory::create();
        $fieldTypeResolver = new FieldTypeResolver($fieldTypeRegistry);
        $simpleTcaSchemaFactory = new SimpleTcaSchemaFactory($fieldTypeResolver);
        $simpleTcaSchemaFactory->initialize($baseTca);
        $contentBlocks = array_map(fn(array $contentBlock) => LoadedContentBlock::fromArray($contentBlock), $contentBlocks);
        $contentBlockRegistry = new ContentBlockRegistry();
        foreach ($contentBlocks as $contentBlock) {
            $contentBlockRegistry->register($contentBlock);
        }
        $contentBlockCompiler = new ContentBlockCompiler();
        $tableDefinitionCollection = (new TableDefinitionCollectionFactory(new NullFrontend('test'), $contentBlockCompiler))
            ->createUncached($contentBlockRegistry, $fieldTypeRegistry, $simpleTcaSchemaFactory);
        $systemExtensionAvailability = new TestSystemExtensionAvailability();
        $systemExtensionAvailability->addAvailableExtension('workspaces');
        if ($seoExtensionLoaded) {
            $systemExtensionAvailability->addAvailableExtension('seo');
        }
        $languageFileRegistry = new NoopLanguageFileRegistry();
        $flexFormGenerator = new FlexFormGenerator($languageFileRegistry);
        $tcaGenerator = new TcaGenerator(
            $tableDefinitionCollection,
            $simpleTcaSchemaFactory,
            $languageFileRegistry,
            $systemExtensionAvailability,
            $flexFormGenerator,
        );

        $tca = $tcaGenerator->generate($baseTca);

        self::assertEquals($expected, $tca);
    }

    #[Test]
    public function missingLabelFieldThrowsException(): void
    {
        $yaml = [
            'name' => 'test/test',
            'extPath' => 'dummyPath',
            'icon' => [
                'iconPath' => '',
                'iconProvider' => '',
            ],
            'yaml' => [
                'table' => 'my_custom_table',
            ],
        ];
        $fieldTypeRegistry = FieldTypeRegistryTestFactory::create();
        $fieldTypeResolver = new FieldTypeResolver($fieldTypeRegistry);
        $simpleTcaSchemaFactory = new SimpleTcaSchemaFactory($fieldTypeResolver);
        $contentBlock = LoadedContentBlock::fromArray($yaml);
        $contentBlockRegistry = new ContentBlockRegistry();
        $contentBlockRegistry->register($contentBlock);
        $contentBlockCompiler = new ContentBlockCompiler();
        $tableDefinitionCollection = (new TableDefinitionCollectionFactory(new NullFrontend('test'), $contentBlockCompiler))
            ->createUncached($contentBlockRegistry, $fieldTypeRegistry, $simpleTcaSchemaFactory);
        $systemExtensionAvailability = new TestSystemExtensionAvailability();
        $systemExtensionAvailability->addAvailableExtension('workspaces');
        $languageFileRegistry = new NoopLanguageFileRegistry();
        $flexFormGenerator = new FlexFormGenerator($languageFileRegistry);
        $tcaGenerator = new TcaGenerator(
            $tableDefinitionCollection,
            $simpleTcaSchemaFactory,
            $languageFileRegistry,
            $systemExtensionAvailability,
            $flexFormGenerator,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1700157578);
        $this->expectExceptionMessage('Option "labelField" is missing for custom table "my_custom_table" and no field could be automatically determined.');

        $tcaGenerator->generate([]);
    }

    public static function checkFlexFormTcaDataProvider(): iterable
    {
        yield 'Two content blocks sharing a new flex form field by disabling prefixes' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_example',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_example',
                        'fields' => [
                            [
                                'identifier' => 'flex',
                                'type' => 'FlexForm',
                                'prefixField' => false,
                                'fields' => [
                                    [
                                        'identifier' => 'header',
                                        'type' => 'Text',
                                    ],
                                    [
                                        'identifier' => 'settings.textarea',
                                        'type' => 'Textarea',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 't3ce/testblock',
                    'extPath' => 'EXT:foo/ContentBlocks/testblock',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_testblock',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_testblock',
                        'fields' => [
                            [
                                'identifier' => 'flex',
                                'type' => 'FlexForm',
                                'prefixField' => false,
                                'fields' => [
                                    [
                                        'identifier' => 'color',
                                        'type' => 'Color',
                                    ],
                                    [
                                        'identifier' => 'link',
                                        'type' => 'Link',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'ctrl' => [
                        'typeicon_classes' => [
                            't3ce_example' => 'tt_content-t3ce_example',
                            't3ce_testblock' => 'tt_content-t3ce_testblock',
                        ],
                        'searchFields' => 'header,header_link,subheader,bodytext,pi_flexform,flex',
                    ],
                    'types' => [
                        't3ce_example' => [
                            'showitem' => 'flex',
                            'previewRenderer' => PreviewRenderer::class,
                            'columnsOverrides' => [
                                'flex' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.description',
                                ],
                            ],
                        ],
                        't3ce_testblock' => [
                            'showitem' => 'flex',
                            'previewRenderer' => PreviewRenderer::class,
                            'columnsOverrides' => [
                                'flex' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/testblock/language/labels.xlf:flex.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/testblock/language/labels.xlf:flex.description',
                                ],
                            ],
                        ],
                    ],
                    'columns' => [
                        'flex' => [
                            'label' => 'flex',
                            'config' => [
                                'type' => 'flex',
                                'ds_pointerField' => 'CType',
                                'ds' => [
                                    'default' => '<T3DataStructure>
  <ROOT>
    <type>array</type>
    <el>
      <xmlTitle>
        <label>The Title:</label>
        <config>
            <type>input</type>
            <size>48</size>
        </config>
      </xmlTitle>
    </el>
  </ROOT>
</T3DataStructure>',
                                    't3ce_example' => '<T3FlexForms>
    <sheets type="array">
        <sDEF type="array">
            <ROOT type="array">
                <type>array</type>
                <el type="array">
                    <field index="header" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.header.label</label>
                        <config type="array">
                            <type>input</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.header.description</description>
                    </field>
                    <field index="settings.textarea" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.settings.textarea.label</label>
                        <config type="array">
                            <type>text</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.settings.textarea.description</description>
                    </field>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3FlexForms>',
                                    't3ce_testblock' => '<T3FlexForms>
    <sheets type="array">
        <sDEF type="array">
            <ROOT type="array">
                <type>array</type>
                <el type="array">
                    <field index="color" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/testblock/language/labels.xlf:flex.color.label</label>
                        <config type="array">
                            <type>color</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/testblock/language/labels.xlf:flex.color.description</description>
                    </field>
                    <field index="link" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/testblock/language/labels.xlf:flex.link.label</label>
                        <config type="array">
                            <type>link</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/testblock/language/labels.xlf:flex.link.description</description>
                    </field>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3FlexForms>',
                                ],
                            ],
                            'exclude' => true,
                        ],
                    ],
                ],
            ],
        ];

        yield 'reusing existing flexForm field' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_example',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_example',
                        'fields' => [
                            [
                                'identifier' => 'pi_flexform',
                                'useExistingField' => true,
                                'fields' => [
                                    [
                                        'type' => 'Text',
                                        'identifier' => 'header',
                                    ],
                                    [
                                        'type' => 'Textarea',
                                        'identifier' => 'textarea',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 't3ce/example2',
                    'extPath' => 'EXT:foo/ContentBlocks/example2',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_example2',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_example2',
                        'fields' => [
                            [
                                'identifier' => 'pi_flexform',
                                'useExistingField' => true,
                                'fields' => [
                                    [
                                        'type' => 'Text',
                                        'identifier' => 'header',
                                    ],
                                    [
                                        'type' => 'Textarea',
                                        'identifier' => 'textarea',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 't3ce/example3',
                    'extPath' => 'EXT:foo/ContentBlocks/example3',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_example3',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_example3',
                        'fields' => [
                            [
                                'identifier' => 'pi_flexform',
                                'useExistingField' => true,
                                'fields' => [
                                    [
                                        'identifier' => 'sheet1',
                                        'type' => 'Sheet',
                                        'fields' => [
                                            [
                                                'identifier' => 'header',
                                                'type' => 'Text',
                                            ],
                                            [
                                                'identifier' => 'textarea',
                                                'type' => 'Textarea',
                                            ],
                                        ],
                                    ],
                                    [
                                        'identifier' => 'sheet2',
                                        'type' => 'Sheet',
                                        'fields' => [
                                            [
                                                'identifier' => 'link',
                                                'type' => 'Link',
                                            ],
                                            [
                                                'identifier' => 'number',
                                                'type' => 'Number',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'ctrl' => [
                        'typeicon_classes' => [
                            't3ce_example' => 'tt_content-t3ce_example',
                            't3ce_example2' => 'tt_content-t3ce_example2',
                            't3ce_example3' => 'tt_content-t3ce_example3',
                        ],
                        'searchFields' => 'header,header_link,subheader,bodytext,pi_flexform',
                    ],
                    'types' => [
                        't3ce_example' => [
                            'showitem' => 'pi_flexform',
                            'previewRenderer' => PreviewRenderer::class,
                            'columnsOverrides' => [
                                'pi_flexform' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:pi_flexform.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:pi_flexform.description',
                                ],
                            ],
                        ],
                        't3ce_example2' => [
                            'showitem' => 'pi_flexform',
                            'previewRenderer' => PreviewRenderer::class,
                            'columnsOverrides' => [
                                'pi_flexform' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example2/language/labels.xlf:pi_flexform.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example2/language/labels.xlf:pi_flexform.description',
                                ],
                            ],
                        ],
                        't3ce_example3' => [
                            'showitem' => 'pi_flexform',
                            'previewRenderer' => PreviewRenderer::class,
                            'columnsOverrides' => [
                                'pi_flexform' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.description',
                                ],
                            ],
                        ],
                    ],
                    'columns' => [
                        'pi_flexform' => [
                            'config' => [
                                'ds' => [
                                    '*,t3ce_example' => '<T3FlexForms>
    <sheets type="array">
        <sDEF type="array">
            <ROOT type="array">
                <type>array</type>
                <el type="array">
                    <field index="header" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:pi_flexform.header.label</label>
                        <config type="array">
                            <type>input</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:pi_flexform.header.description</description>
                    </field>
                    <field index="textarea" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:pi_flexform.textarea.label</label>
                        <config type="array">
                            <type>text</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:pi_flexform.textarea.description</description>
                    </field>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3FlexForms>',
                                    '*,t3ce_example2' => '<T3FlexForms>
    <sheets type="array">
        <sDEF type="array">
            <ROOT type="array">
                <type>array</type>
                <el type="array">
                    <field index="header" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example2/language/labels.xlf:pi_flexform.header.label</label>
                        <config type="array">
                            <type>input</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example2/language/labels.xlf:pi_flexform.header.description</description>
                    </field>
                    <field index="textarea" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example2/language/labels.xlf:pi_flexform.textarea.label</label>
                        <config type="array">
                            <type>text</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example2/language/labels.xlf:pi_flexform.textarea.description</description>
                    </field>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3FlexForms>',
                                    '*,t3ce_example3' => '<T3FlexForms>
    <sheets type="array">
        <sheet1 type="array">
            <ROOT type="array">
                <type>array</type>
                <el type="array">
                    <field index="header" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.header.label</label>
                        <config type="array">
                            <type>input</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.header.description</description>
                    </field>
                    <field index="textarea" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.textarea.label</label>
                        <config type="array">
                            <type>text</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.textarea.description</description>
                    </field>
                </el>
                <sheetTitle>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.sheets.sheet1.label</sheetTitle>
                <sheetDescription>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.sheets.sheet1.description</sheetDescription>
                <sheetShortDescr>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.sheets.sheet1.linkTitle</sheetShortDescr>
            </ROOT>
        </sheet1>
        <sheet2 type="array">
            <ROOT type="array">
                <type>array</type>
                <el type="array">
                    <field index="link" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.link.label</label>
                        <config type="array">
                            <type>link</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.link.description</description>
                    </field>
                    <field index="number" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.number.label</label>
                        <config type="array">
                            <type>number</type>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.number.description</description>
                    </field>
                </el>
                <sheetTitle>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.sheets.sheet2.label</sheetTitle>
                <sheetDescription>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.sheets.sheet2.description</sheetDescription>
                <sheetShortDescr>LLL:EXT:foo/ContentBlocks/example3/language/labels.xlf:pi_flexform.sheets.sheet2.linkTitle</sheetShortDescr>
            </ROOT>
        </sheet2>
    </sheets>
</T3FlexForms>',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'FlexForm sections and container are created' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_example',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_example',
                        'fields' => [
                            [
                                'identifier' => 'flex',
                                'type' => 'FlexForm',
                                'prefixField' => false,
                                'fields' => [
                                    [
                                        'identifier' => 'section1',
                                        'type' => 'Section',
                                        'container' => [
                                            [
                                                'identifier' => 'container1',
                                                'fields' => [
                                                    [
                                                        'identifier' => 'header',
                                                        'type' => 'Text',
                                                    ],
                                                    [
                                                        'identifier' => 'textarea',
                                                        'type' => 'Textarea',
                                                    ],
                                                ],
                                            ],
                                            [
                                                'identifier' => 'container2',
                                                'fields' => [
                                                    [
                                                        'identifier' => 'header2',
                                                        'type' => 'Text',
                                                    ],
                                                    [
                                                        'identifier' => 'textarea2',
                                                        'type' => 'Textarea',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'ctrl' => [
                        'typeicon_classes' => [
                            't3ce_example' => 'tt_content-t3ce_example',
                        ],
                        'searchFields' => 'header,header_link,subheader,bodytext,pi_flexform,flex',
                    ],
                    'types' => [
                        't3ce_example' => [
                            'showitem' => 'flex',
                            'previewRenderer' => PreviewRenderer::class,
                            'columnsOverrides' => [
                                'flex' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.description',
                                ],
                            ],
                        ],
                    ],
                    'columns' => [
                        'flex' => [
                            'label' => 'flex',
                            'config' => [
                                'type' => 'flex',
                                'ds_pointerField' => 'CType',
                                'ds' => [
                                    'default' => '<T3DataStructure>
  <ROOT>
    <type>array</type>
    <el>
      <xmlTitle>
        <label>The Title:</label>
        <config>
            <type>input</type>
            <size>48</size>
        </config>
      </xmlTitle>
    </el>
  </ROOT>
</T3DataStructure>',
                                    't3ce_example' => '<T3FlexForms>
    <sheets type="array">
        <sDEF type="array">
            <ROOT type="array">
                <type>array</type>
                <el type="array">
                    <field index="section1" type="array">
                        <title>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.title</title>
                        <type>array</type>
                        <section>1</section>
                        <el type="array">
                            <field index="container1" type="array">
                                <title>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.container.container1.title</title>
                                <type>array</type>
                                <el type="array">
                                    <field index="header" type="array">
                                        <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.container.container1.header.label</label>
                                        <config type="array">
                                            <type>input</type>
                                        </config>
                                        <description>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.container.container1.header.description</description>
                                    </field>
                                    <field index="textarea" type="array">
                                        <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.container.container1.textarea.label</label>
                                        <config type="array">
                                            <type>text</type>
                                        </config>
                                        <description>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.container.container1.textarea.description</description>
                                    </field>
                                </el>
                            </field>
                            <field index="container2" type="array">
                                <title>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.container.container2.title</title>
                                <type>array</type>
                                <el type="array">
                                    <field index="header2" type="array">
                                        <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.container.container2.header2.label</label>
                                        <config type="array">
                                            <type>input</type>
                                        </config>
                                        <description>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.container.container2.header2.description</description>
                                    </field>
                                    <field index="textarea2" type="array">
                                        <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.container.container2.textarea2.label</label>
                                        <config type="array">
                                            <type>text</type>
                                        </config>
                                        <description>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.sections.section1.container.container2.textarea2.description</description>
                                    </field>
                                </el>
                            </field>
                        </el>
                    </field>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3FlexForms>',
                                ],
                            ],
                            'exclude' => true,
                        ],
                    ],
                ],
            ],
        ];

        yield 'FlexForm automatic labels for items work' => [
            'contentBlocks' => [
                [
                    'name' => 't3ce/example',
                    'extPath' => 'EXT:foo/ContentBlocks/example',
                    'icon' => [
                        'iconPath' => '',
                        'iconProvider' => '',
                        'iconIdentifier' => 'tt_content-t3ce_example',
                    ],
                    'yaml' => [
                        'table' => 'tt_content',
                        'typeField' => 'CType',
                        'typeName' => 't3ce_example',
                        'fields' => [
                            [
                                'identifier' => 'flex',
                                'type' => 'FlexForm',
                                'prefixField' => false,
                                'fields' => [
                                    [
                                        'identifier' => 'select',
                                        'type' => 'Select',
                                        'renderType' => 'selectSingle',
                                        'items' => [
                                            [
                                                'value' => '',
                                            ],
                                            [
                                                'value' => '1',
                                            ],
                                            [
                                                'value' => '2',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'ctrl' => [
                        'typeicon_classes' => [
                            't3ce_example' => 'tt_content-t3ce_example',
                        ],
                        'searchFields' => 'header,header_link,subheader,bodytext,pi_flexform,flex',
                    ],
                    'types' => [
                        't3ce_example' => [
                            'showitem' => 'flex',
                            'previewRenderer' => PreviewRenderer::class,
                            'columnsOverrides' => [
                                'flex' => [
                                    'label' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.label',
                                    'description' => 'LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.description',
                                ],
                            ],
                        ],
                    ],
                    'columns' => [
                        'flex' => [
                            'label' => 'flex',
                            'config' => [
                                'type' => 'flex',
                                'ds_pointerField' => 'CType',
                                'ds' => [
                                    'default' => '<T3DataStructure>
  <ROOT>
    <type>array</type>
    <el>
      <xmlTitle>
        <label>The Title:</label>
        <config>
            <type>input</type>
            <size>48</size>
        </config>
      </xmlTitle>
    </el>
  </ROOT>
</T3DataStructure>',
                                    't3ce_example' => '<T3FlexForms>
    <sheets type="array">
        <sDEF type="array">
            <ROOT type="array">
                <type>array</type>
                <el type="array">
                    <field index="select" type="array">
                        <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.select.label</label>
                        <config type="array">
                            <renderType>selectSingle</renderType>
                            <type>select</type>
                            <items type="array">
                                <numIndex index="0" type="array">
                                    <value></value>
                                    <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.select.items.label</label>
                                </numIndex>
                                <numIndex index="1" type="array">
                                    <value>1</value>
                                    <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.select.items.1.label</label>
                                </numIndex>
                                <numIndex index="2" type="array">
                                    <value>2</value>
                                    <label>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.select.items.2.label</label>
                                </numIndex>
                            </items>
                        </config>
                        <description>LLL:EXT:foo/ContentBlocks/example/language/labels.xlf:flex.select.description</description>
                    </field>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3FlexForms>',
                                ],
                            ],
                            'exclude' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('checkFlexFormTcaDataProvider')]
    #[Test]
    public function checkFlexFormTca(array $contentBlocks, array $expected): void
    {
        $baseTca['tt_content']['ctrl']['type'] = 'CType';
        $baseTca['tt_content']['columns']['pi_flexform'] = [
            'label' => 'FlexForm',
            'config' => [
                'type' => 'flex',
                'ds_pointerField' => 'list_type,CType',
                'ds' => [
                    'default' => '<T3DataStructure><!-- example --></T3DataStructure>',
                ],
            ],
        ];
        $baseTca['tt_content']['ctrl']['searchFields'] = 'header,header_link,subheader,bodytext,pi_flexform';

        $fieldTypeRegistry = FieldTypeRegistryTestFactory::create();
        $fieldTypeResolver = new FieldTypeResolver($fieldTypeRegistry);
        $simpleTcaSchemaFactory = new SimpleTcaSchemaFactory($fieldTypeResolver);
        $simpleTcaSchemaFactory->initialize($baseTca);
        $contentBlockRegistry = new ContentBlockRegistry();
        foreach ($contentBlocks as $contentBlock) {
            $contentBlockRegistry->register(LoadedContentBlock::fromArray($contentBlock));
        }
        $contentBlockCompiler = new ContentBlockCompiler();
        $tableDefinitionCollection = (new TableDefinitionCollectionFactory(new NullFrontend('test'), $contentBlockCompiler))
            ->createUncached($contentBlockRegistry, $fieldTypeRegistry, $simpleTcaSchemaFactory);
        $systemExtensionAvailability = new TestSystemExtensionAvailability();
        $systemExtensionAvailability->addAvailableExtension('workspaces');
        $languageFileRegistry = new NoopLanguageFileRegistry();
        $flexFormGenerator = new FlexFormGenerator($languageFileRegistry);
        $tcaGenerator = new TcaGenerator(
            $tableDefinitionCollection,
            $simpleTcaSchemaFactory,
            $languageFileRegistry,
            $systemExtensionAvailability,
            $flexFormGenerator,
        );

        $tca = $tcaGenerator->generate($baseTca);

        self::assertEquals($expected, $tca);
    }

    #[Test]
    public function displayCondIsPrefixedForStringSyntax(): void
    {
        $baseTca['tt_content'] = [];

        $contentBlock = LoadedContentBlock::fromArray([
            'name' => 'bar/foo',
            'yaml' => [
                'table' => 'tt_content',
                'prefixFields' => true,
                'prefixType' => 'full',
                'fields' => [
                    [
                        'identifier' => 'aField',
                        'displayCond' => 'FIELD:bField:=:aValue',
                        'type' => 'Text',
                    ],
                    [
                        'identifier' => 'bField',
                        'type' => 'Text',
                    ],
                ],
            ],
        ]);

        $expected = 'FIELD:bar_foo_bField:=:aValue';

        $fieldTypeRegistry = FieldTypeRegistryTestFactory::create();
        $fieldTypeResolver = new FieldTypeResolver($fieldTypeRegistry);
        $simpleTcaSchemaFactory = new SimpleTcaSchemaFactory($fieldTypeResolver);
        $simpleTcaSchemaFactory->initialize($baseTca);
        $contentBlockRegistry = new ContentBlockRegistry();
        $contentBlockRegistry->register($contentBlock);
        $contentBlockCompiler = new ContentBlockCompiler();
        $tableDefinitionCollection = (new TableDefinitionCollectionFactory(new NullFrontend('test'), $contentBlockCompiler))
            ->createUncached($contentBlockRegistry, $fieldTypeRegistry, $simpleTcaSchemaFactory);
        $systemExtensionAvailability = new TestSystemExtensionAvailability();
        $systemExtensionAvailability->addAvailableExtension('workspaces');
        $languageFileRegistry = new NoopLanguageFileRegistry();
        $flexFormGenerator = new FlexFormGenerator($languageFileRegistry);
        $tcaGenerator = new TcaGenerator(
            $tableDefinitionCollection,
            $simpleTcaSchemaFactory,
            $languageFileRegistry,
            $systemExtensionAvailability,
            $flexFormGenerator,
        );

        $tca = $tcaGenerator->generate($baseTca);
        $actual = $tca['tt_content']['types']['1']['columnsOverrides']['bar_foo_aField']['displayCond'];

        self::assertEquals($expected, $actual);
    }

    public static function existingTablesCanBeExtendedWithAdditionalTypeDataProvider(): iterable
    {
        yield 'Additional type is added' => [
            'baseTca' => [
                'existing_record' => [
                    'ctrl' => [
                        'type' => 'record_type',
                        'descriptionColumn' => 'a_description_column',
                        'enablecolumns' => [
                            'disabled' => 'a_hidden_field',
                            'endtime' => 'a_endtime_field',
                            'starttime' => 'a_starttime_field',
                            'fe_group' => 'a_fe_group_field',
                        ],
                        'typeicon_classes' => [
                            'type_1' => 'type_1',
                            'default' => 'type_1',
                        ],
                    ],
                    'types' => [
                        'type_1' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,record_type,a_hidden_field,a_endtime_field,a_starttime_field,a_fe_group_field,a_description_column',
                        ],
                    ],
                    'columns' => [
                        'record_type' => [
                            'label' => 'Type',
                            'config' => [
                                'type' => 'select',
                                'renderType' => 'selectSingle',
                                'items' => [
                                    [
                                        'label' => 'Type 1',
                                        'value' => 'type_1',
                                    ],
                                ],
                            ],
                        ],
                        'a_hidden_field' => [
                            'label' => 'Hidden',
                            'config' => [
                                'type' => 'check',
                            ],
                        ],
                        'a_endtime_field' => [
                            'label' => 'Endtime',
                            'config' => [
                                'type' => 'datetime',
                            ],
                        ],
                        'a_starttime_field' => [
                            'label' => 'Starttime',
                            'config' => [
                                'type' => 'datetime',
                            ],
                        ],
                        'a_description_column' => [
                            'label' => 'Notes',
                            'config' => [
                                'type' => 'text',
                            ],
                        ],
                        'a_fe_group_field' => [
                            'label' => 'Notes',
                            'config' => [
                                'type' => 'select',
                                'renderType' => 'selectMultipleSideBySide',
                            ],
                        ],
                    ],
                ],
            ],
            'contentBlockArray' => [
                'name' => 'my-vendor/record2',
                'hostExtension' => 'my_extension',
                'extPath' => 'EXT:my_extension/ContentBlocks/RecordType/record',
                'icon' => [
                    'iconIdentifier' => 'existing_record-type_2',
                ],
                'yaml' => [
                    'table' => 'existing_record',
                    'typeName' => 'type_2',
                    'prefixFields' => false,
                    'fields' => [
                        [
                            'identifier' => 'a_field',
                            'type' => 'Text',
                        ],
                    ],
                ],
            ],
            'expected' => [
                'existing_record' => [
                    'ctrl' => [
                        'type' => 'record_type',
                        'descriptionColumn' => 'a_description_column',
                        'enablecolumns' => [
                            'disabled' => 'a_hidden_field',
                            'endtime' => 'a_endtime_field',
                            'starttime' => 'a_starttime_field',
                            'fe_group' => 'a_fe_group_field',
                        ],
                        'typeicon_classes' => [
                            'type_1' => 'type_1',
                            'default' => 'type_1',
                            'type_2' => 'existing_record-type_2',
                        ],
                        'typeicon_column' => 'record_type',
                        'searchFields' => 'a_field',
                    ],
                    'types' => [
                        'type_1' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,record_type,a_hidden_field,a_endtime_field,a_starttime_field,a_fe_group_field,a_description_column',
                        ],
                        'type_2' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,record_type,a_field,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,a_description_column',
                            'columnsOverrides' => [
                                'a_field' => [
                                    'label' => 'LLL:EXT:my_extension/ContentBlocks/RecordType/record/language/labels.xlf:a_field.label',
                                    'description' => 'LLL:EXT:my_extension/ContentBlocks/RecordType/record/language/labels.xlf:a_field.description',
                                ],
                            ],
                        ],
                    ],
                    'columns' => [
                        'record_type' => [
                            'label' => 'Type',
                            'config' => [
                                'type' => 'select',
                                'renderType' => 'selectSingle',
                                'items' => [
                                    [
                                        'label' => 'Type 1',
                                        'value' => 'type_1',
                                    ],
                                    [
                                        'label' => 'LLL:EXT:my_extension/ContentBlocks/RecordType/record/language/labels.xlf:title',
                                        'value' => 'type_2',
                                        'icon' => 'existing_record-type_2',
                                        'group' => null,
                                        'description' => 'LLL:EXT:my_extension/ContentBlocks/RecordType/record/language/labels.xlf:description',
                                    ],
                                ],
                            ],
                            'exclude' => true,
                        ],
                        'a_hidden_field' => [
                            'label' => 'Hidden',
                            'config' => [
                                'type' => 'check',
                            ],
                        ],
                        'a_endtime_field' => [
                            'label' => 'Endtime',
                            'config' => [
                                'type' => 'datetime',
                            ],
                        ],
                        'a_starttime_field' => [
                            'label' => 'Starttime',
                            'config' => [
                                'type' => 'datetime',
                            ],
                        ],
                        'a_description_column' => [
                            'label' => 'Notes',
                            'config' => [
                                'type' => 'text',
                            ],
                        ],
                        'a_fe_group_field' => [
                            'label' => 'Notes',
                            'config' => [
                                'type' => 'select',
                                'renderType' => 'selectMultipleSideBySide',
                            ],
                        ],
                        'a_field' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'exclude' => true,
                            'label' => 'a_field',
                        ],
                    ],
                    'palettes' => [
                        'hidden' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
                            'showitem' => 'a_hidden_field',
                        ],
                        'access' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                            'showitem' => 'a_starttime_field;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,a_endtime_field;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,--linebreak--,a_fe_group_field;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Table without type enriched with type field and icons' => [
            'baseTca' => [
                'existing_record' => [
                    'ctrl' => [
                        'descriptionColumn' => 'a_description_column',
                        'enablecolumns' => [
                            'disabled' => 'a_hidden_field',
                            'endtime' => 'a_endtime_field',
                            'starttime' => 'a_starttime_field',
                            'fe_group' => 'a_fe_group_field',
                        ],
                    ],
                    'types' => [
                        '0' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,record_type,a_hidden_field,a_endtime_field,a_starttime_field,a_fe_group_field,a_description_column',
                        ],
                    ],
                    'columns' => [
                        'a_hidden_field' => [
                            'label' => 'Hidden',
                            'config' => [
                                'type' => 'check',
                            ],
                        ],
                        'a_endtime_field' => [
                            'label' => 'Endtime',
                            'config' => [
                                'type' => 'datetime',
                            ],
                        ],
                        'a_starttime_field' => [
                            'label' => 'Starttime',
                            'config' => [
                                'type' => 'datetime',
                            ],
                        ],
                        'a_description_column' => [
                            'label' => 'Notes',
                            'config' => [
                                'type' => 'text',
                            ],
                        ],
                        'a_fe_group_field' => [
                            'label' => 'Notes',
                            'config' => [
                                'type' => 'select',
                                'renderType' => 'selectMultipleSideBySide',
                            ],
                        ],
                    ],
                ],
            ],
            'contentBlockArray' => [
                'name' => 'my-vendor/record2',
                'hostExtension' => 'my_extension',
                'extPath' => 'EXT:my_extension/ContentBlocks/RecordType/record',
                'icon' => [
                    'iconIdentifier' => 'existing_record-type_2',
                ],
                'yaml' => [
                    'table' => 'existing_record',
                    'typeField' => 'record_type',
                    'typeName' => 'type_2',
                    'prefixFields' => false,
                    'fields' => [
                        [
                            'identifier' => 'a_field',
                            'type' => 'Text',
                        ],
                    ],
                ],
            ],
            'expected' => [
                'existing_record' => [
                    'ctrl' => [
                        'descriptionColumn' => 'a_description_column',
                        'enablecolumns' => [
                            'disabled' => 'a_hidden_field',
                            'endtime' => 'a_endtime_field',
                            'starttime' => 'a_starttime_field',
                            'fe_group' => 'a_fe_group_field',
                        ],
                        'typeicon_classes' => [
                            'type_2' => 'existing_record-type_2',
                            'default' => 'existing_record-type_2',
                        ],
                        'type' => 'record_type',
                        'typeicon_column' => 'record_type',
                        'searchFields' => 'a_field',
                    ],
                    'types' => [
                        '0' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,record_type,a_hidden_field,a_endtime_field,a_starttime_field,a_fe_group_field,a_description_column',
                        ],
                        'type_2' => [
                            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,record_type,a_field,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,a_description_column',
                            'columnsOverrides' => [
                                'a_field' => [
                                    'label' => 'LLL:EXT:my_extension/ContentBlocks/RecordType/record/language/labels.xlf:a_field.label',
                                    'description' => 'LLL:EXT:my_extension/ContentBlocks/RecordType/record/language/labels.xlf:a_field.description',
                                ],
                            ],
                        ],
                    ],
                    'columns' => [
                        'a_hidden_field' => [
                            'label' => 'Hidden',
                            'config' => [
                                'type' => 'check',
                            ],
                        ],
                        'a_endtime_field' => [
                            'label' => 'Endtime',
                            'config' => [
                                'type' => 'datetime',
                            ],
                        ],
                        'a_starttime_field' => [
                            'label' => 'Starttime',
                            'config' => [
                                'type' => 'datetime',
                            ],
                        ],
                        'a_description_column' => [
                            'label' => 'Notes',
                            'config' => [
                                'type' => 'text',
                            ],
                        ],
                        'a_fe_group_field' => [
                            'label' => 'Notes',
                            'config' => [
                                'type' => 'select',
                                'renderType' => 'selectMultipleSideBySide',
                            ],
                        ],
                        'record_type' => [
                            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.type',
                            'exclude' => true,
                            'config' => [
                                'renderType' => 'selectSingle',
                                'type' => 'select',
                                'default' => 'type_2',
                                'items' => [
                                    [
                                        'label' => 'LLL:EXT:my_extension/ContentBlocks/RecordType/record/language/labels.xlf:title',
                                        'value' => 'type_2',
                                        'icon' => 'existing_record-type_2',
                                        'group' => null,
                                        'description' => 'LLL:EXT:my_extension/ContentBlocks/RecordType/record/language/labels.xlf:description',
                                    ],
                                ],
                            ],
                        ],
                        'a_field' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'exclude' => true,
                            'label' => 'a_field',
                        ],
                    ],
                    'palettes' => [
                        'hidden' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.visibility',
                            'showitem' => 'a_hidden_field',
                        ],
                        'access' => [
                            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                            'showitem' => 'a_starttime_field;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,a_endtime_field;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,--linebreak--,a_fe_group_field;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel',
                        ],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('existingTablesCanBeExtendedWithAdditionalTypeDataProvider')]
    #[Test]
    public function existingTablesCanBeExtendedWithAdditionalType(array $baseTca, array $contentBlockArray, array $expected): void
    {
        $fieldTypeRegistry = FieldTypeRegistryTestFactory::create();
        $fieldTypeResolver = new FieldTypeResolver($fieldTypeRegistry);
        $simpleTcaSchemaFactory = new SimpleTcaSchemaFactory($fieldTypeResolver);
        $simpleTcaSchemaFactory->initialize($baseTca);
        $contentBlockRegistry = new ContentBlockRegistry();
        $contentBlock = LoadedContentBlock::fromArray($contentBlockArray);
        $contentBlockRegistry->register($contentBlock);
        $contentBlockCompiler = new ContentBlockCompiler();
        $tableDefinitionCollection = (new TableDefinitionCollectionFactory(new NullFrontend('test'), $contentBlockCompiler))
            ->createUncached($contentBlockRegistry, $fieldTypeRegistry, $simpleTcaSchemaFactory);
        $systemExtensionAvailability = new TestSystemExtensionAvailability();
        $languageFileRegistry = new NoopLanguageFileRegistry();
        $flexFormGenerator = new FlexFormGenerator($languageFileRegistry);
        $tcaGenerator = new TcaGenerator(
            $tableDefinitionCollection,
            $simpleTcaSchemaFactory,
            $languageFileRegistry,
            $systemExtensionAvailability,
            $flexFormGenerator,
        );

        $beforeTcaOverridesEvent = new BeforeTcaOverridesEvent($baseTca);
        $tcaGenerator($beforeTcaOverridesEvent);
        $tca = $beforeTcaOverridesEvent->getTca();

        self::assertSame($expected, $tca);
    }
}
