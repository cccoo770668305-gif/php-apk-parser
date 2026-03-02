<?php

namespace ApkParser;

use ApkParser\Exceptions\XmlParserException;

/**
 * This file is part of the Apk Parser package.
 *
 * (c) Tufan Baris Yildirim <tufanbarisyildirim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class XmlParser
{
    public const RES_NULL_TYPE = 0x0000;
    public const RES_STRING_POOL_TYPE = 0x0001;
    public const RES_TABLE_TYPE = 0x0002;
    public const RES_XML_TYPE = 0x0003;

    public const TAG_NULL = 0x0000;
    public const TAG_DOC_START = 0x0100;
    public const TAG_DOC_END = 0x0101;
    public const TAG_START = 0x0102;
    public const TAG_END = 0x0103;
    public const TAG_TEXT = 0x0104;


    public const RES_XML_START_ELEMENT_TYPE = 0x0102;
    public const RES_XML_RESOURCE_MAP_TYPE = 0x0180;

    public const UTF8_FLAG = 0x00000100;
    public static $indent_spaces = "                                             ";
    private $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
    private $bytes = array();
    private $ready = false;
    private $isUTF8 = false;
    private $nsNum = 0;
    /**
     * Store the SimpleXmlElement object
     * @var \SimpleXmlElement
     */
    private $xmlObject = null;

    /**
     * These values are taken from the Android Manifest class.
     */
    private static $resourceMap = [
        0x1010000 => 'theme',
        0x1010001 => 'label',
        0x1010002 => 'icon',
        0x1010003 => 'name',
        0x1010004 => 'manageSpaceActivity',
        0x1010005 => 'allowClearUserData',
        0x1010006 => 'permission',
        0x1010007 => 'readPermission',
        0x1010008 => 'writePermission',
        0x1010009 => 'protectionLevel',
        0x101000a => 'permissionGroup',
        0x101000b => 'sharedUserId',
        0x101000c => 'hasCode',
        0x101000d => 'persistent',
        0x101000e => 'enabled',
        0x101000f => 'debuggable',
        0x1010010 => 'exported',
        0x1010011 => 'process',
        0x1010012 => 'taskAffinity',
        0x1010013 => 'multiprocess',
        0x1010014 => 'finishOnTaskLaunch',
        0x1010015 => 'clearTaskOnLaunch',
        0x1010016 => 'stateNotNeeded',
        0x1010017 => 'excludeFromRecents',
        0x1010018 => 'authorities',
        0x1010019 => 'syncable',
        0x101001a => 'initOrder',
        0x101001b => 'grantUriPermissions',
        0x101001c => 'priority',
        0x101001d => 'launchMode',
        0x101001e => 'screenOrientation',
        0x101001f => 'configChanges',
        0x1010020 => 'description',
        0x1010021 => 'targetPackage',
        0x1010022 => 'handleProfiling',
        0x1010023 => 'functionalTest',
        0x1010024 => 'value',
        0x1010025 => 'resource',
        0x1010026 => 'mimeType',
        0x1010027 => 'scheme',
        0x1010028 => 'host',
        0x1010029 => 'port',
        0x101002a => 'path',
        0x101002b => 'pathPrefix',
        0x101002c => 'pathPattern',
        0x101002d => 'action',
        0x101002e => 'data',
        0x101002f => 'targetClass',
        0x1010030 => 'colorForeground',
        0x1010031 => 'colorBackground',
        0x1010032 => 'backgroundDimAmount',
        0x1010033 => 'disabledAlpha',
        0x1010034 => 'textAppearance',
        0x1010035 => 'textAppearanceInverse',
        0x1010036 => 'textColorPrimary',
        0x1010037 => 'textColorPrimaryDisableOnly',
        0x1010038 => 'textColorSecondary',
        0x1010039 => 'textColorPrimaryInverse',
        0x101003a => 'textColorSecondaryInverse',
        0x101003b => 'textColorPrimaryNoDisable',
        0x101003c => 'textColorSecondaryNoDisable',
        0x101003d => 'textColorPrimaryInverseNoDisable',
        0x101003e => 'textColorSecondaryInverseNoDisable',
        0x101003f => 'textColorHintInverse',
        0x1010040 => 'textAppearanceLarge',
        0x1010041 => 'textAppearanceMedium',
        0x1010042 => 'textAppearanceSmall',
        0x1010043 => 'textAppearanceLargeInverse',
        0x1010044 => 'textAppearanceMediumInverse',
        0x1010045 => 'textAppearanceSmallInverse',
        0x1010046 => 'textCheckMark',
        0x1010047 => 'textCheckMarkInverse',
        0x1010048 => 'buttonStyle',
        0x1010049 => 'buttonStyleSmall',
        0x101004a => 'buttonStyleInset',
        0x101004b => 'buttonStyleToggle',
        0x101004c => 'galleryItemBackground',
        0x101004d => 'listPreferredItemHeight',
        0x101004e => 'expandableListPreferredItemPaddingLeft',
        0x101004f => 'expandableListPreferredChildPaddingLeft',
        0x1010050 => 'expandableListPreferredItemIndicatorLeft',
        0x1010051 => 'expandableListPreferredItemIndicatorRight',
        0x1010052 => 'expandableListPreferredChildIndicatorLeft',
        0x1010053 => 'expandableListPreferredChildIndicatorRight',
        0x1010054 => 'windowBackground',
        0x1010055 => 'windowFrame',
        0x1010056 => 'windowNoTitle',
        0x1010057 => 'windowIsFloating',
        0x1010058 => 'windowIsTranslucent',
        0x1010059 => 'windowContentOverlay',
        0x101005a => 'windowTitleSize',
        0x101005b => 'windowTitleStyle',
        0x101005c => 'windowTitleBackgroundStyle',
        0x101005d => 'alertDialogStyle',
        0x101005e => 'panelBackground',
        0x101005f => 'panelFullBackground',
        0x1010060 => 'panelColorForeground',
        0x1010061 => 'panelColorBackground',
        0x1010062 => 'panelTextAppearance',
        0x1010063 => 'scrollbarSize',
        0x1010064 => 'scrollbarThumbHorizontal',
        0x1010065 => 'scrollbarThumbVertical',
        0x1010066 => 'scrollbarTrackHorizontal',
        0x1010067 => 'scrollbarTrackVertical',
        0x1010068 => 'scrollbarAlwaysDrawHorizontalTrack',
        0x1010069 => 'scrollbarAlwaysDrawVerticalTrack',
        0x101006a => 'absListViewStyle',
        0x101006b => 'autoCompleteTextViewStyle',
        0x101006c => 'checkboxStyle',
        0x101006d => 'dropDownListViewStyle',
        0x101006e => 'editTextStyle',
        0x101006f => 'expandableListViewStyle',
        0x1010070 => 'galleryStyle',
        0x1010071 => 'gridViewStyle',
        0x1010072 => 'imageButtonStyle',
        0x1010073 => 'imageWellStyle',
        0x1010074 => 'listViewStyle',
        0x1010075 => 'listViewWhiteStyle',
        0x1010076 => 'popupWindowStyle',
        0x1010077 => 'progressBarStyle',
        0x1010078 => 'progressBarStyleHorizontal',
        0x1010079 => 'progressBarStyleSmall',
        0x101007a => 'progressBarStyleLarge',
        0x101007b => 'seekBarStyle',
        0x101007c => 'ratingBarStyle',
        0x101007d => 'ratingBarStyleSmall',
        0x101007e => 'radioButtonStyle',
        0x101007f => 'scrollbarStyle',
        0x1010080 => 'scrollViewStyle',
        0x1010081 => 'spinnerStyle',
        0x1010082 => 'starStyle',
        0x1010083 => 'tabWidgetStyle',
        0x1010084 => 'textViewStyle',
        0x1010085 => 'webViewStyle',
        0x1010086 => 'dropDownItemStyle',
        0x1010087 => 'spinnerDropDownItemStyle',
        0x1010088 => 'dropDownHintAppearance',
        0x1010089 => 'spinnerItemStyle',
        0x101008a => 'mapViewStyle',
        0x101008b => 'preferenceScreenStyle',
        0x101008c => 'preferenceCategoryStyle',
        0x101008d => 'preferenceInformationStyle',
        0x101008e => 'preferenceStyle',
        0x101008f => 'checkBoxPreferenceStyle',
        0x1010090 => 'yesNoPreferenceStyle',
        0x1010091 => 'dialogPreferenceStyle',
        0x1010092 => 'editTextPreferenceStyle',
        0x1010093 => 'ringtonePreferenceStyle',
        0x1010094 => 'preferenceLayoutChild',
        0x1010095 => 'textSize',
        0x1010096 => 'typeface',
        0x1010097 => 'textStyle',
        0x1010098 => 'textColor',
        0x1010099 => 'textColorHighlight',
        0x101009a => 'textColorHint',
        0x101009b => 'textColorLink',
        0x101009c => 'state_focused',
        0x101009d => 'state_window_focused',
        0x101009e => 'state_enabled',
        0x101009f => 'state_checkable',
        0x10100a0 => 'state_checked',
        0x10100a1 => 'state_selected',
        0x10100a2 => 'state_active',
        0x10100a3 => 'state_single',
        0x10100a4 => 'state_first',
        0x10100a5 => 'state_middle',
        0x10100a6 => 'state_last',
        0x10100a7 => 'state_pressed',
        0x10100a8 => 'state_expanded',
        0x10100a9 => 'state_empty',
        0x10100aa => 'state_above_anchor',
        0x10100ab => 'ellipsize',
        0x10100ac => 'x',
        0x10100ad => 'y',
        0x10100ae => 'windowAnimationStyle',
        0x10100af => 'gravity',
        0x10100b0 => 'autoLink',
        0x10100b1 => 'linksClickable',
        0x10100b2 => 'entries',
        0x10100b3 => 'layout_gravity',
        0x10100b4 => 'windowEnterAnimation',
        0x10100b5 => 'windowExitAnimation',
        0x10100b6 => 'windowShowAnimation',
        0x10100b7 => 'windowHideAnimation',
        0x10100b8 => 'activityOpenEnterAnimation',
        0x10100b9 => 'activityOpenExitAnimation',
        0x10100ba => 'activityCloseEnterAnimation',
        0x10100bb => 'activityCloseExitAnimation',
        0x10100bc => 'taskOpenEnterAnimation',
        0x10100bd => 'taskOpenExitAnimation',
        0x10100be => 'taskCloseEnterAnimation',
        0x10100bf => 'taskCloseExitAnimation',
        0x10100c0 => 'taskToFrontEnterAnimation',
        0x10100c1 => 'taskToFrontExitAnimation',
        0x10100c2 => 'taskToBackEnterAnimation',
        0x10100c3 => 'taskToBackExitAnimation',
        0x10100c4 => 'orientation',
        0x10100c5 => 'keycode',
        0x10100c6 => 'fullDark',
        0x10100c7 => 'topDark',
        0x10100c8 => 'centerDark',
        0x10100c9 => 'bottomDark',
        0x10100ca => 'fullBright',
        0x10100cb => 'topBright',
        0x10100cc => 'centerBright',
        0x10100cd => 'bottomBright',
        0x10100ce => 'bottomMedium',
        0x10100cf => 'centerMedium',
        0x10100d0 => 'id',
        0x10100d1 => 'tag',
        0x10100d2 => 'scrollX',
        0x10100d3 => 'scrollY',
        0x10100d4 => 'background',
        0x10100d5 => 'padding',
        0x10100d6 => 'paddingLeft',
        0x10100d7 => 'paddingTop',
        0x10100d8 => 'paddingRight',
        0x10100d9 => 'paddingBottom',
        0x10100da => 'focusable',
        0x10100db => 'focusableInTouchMode',
        0x10100dc => 'visibility',
        0x10100dd => 'fitsSystemWindows',
        0x10100de => 'scrollbars',
        0x10100df => 'fadingEdge',
        0x10100e0 => 'fadingEdgeLength',
        0x10100e1 => 'nextFocusLeft',
        0x10100e2 => 'nextFocusRight',
        0x10100e3 => 'nextFocusUp',
        0x10100e4 => 'nextFocusDown',
        0x10100e5 => 'clickable',
        0x10100e6 => 'longClickable',
        0x10100e7 => 'saveEnabled',
        0x10100e8 => 'drawingCacheQuality',
        0x10100e9 => 'duplicateParentState',
        0x10100ea => 'clipChildren',
        0x10100eb => 'clipToPadding',
        0x10100ec => 'layoutAnimation',
        0x10100ed => 'animationCache',
        0x10100ee => 'persistentDrawingCache',
        0x10100ef => 'alwaysDrawnWithCache',
        0x10100f0 => 'addStatesFromChildren',
        0x10100f1 => 'descendantFocusability',
        0x10100f2 => 'layout',
        0x10100f3 => 'inflatedId',
        0x10100f4 => 'layout_width',
        0x10100f5 => 'layout_height',
        0x10100f6 => 'layout_margin',
        0x10100f7 => 'layout_marginLeft',
        0x10100f8 => 'layout_marginTop',
        0x10100f9 => 'layout_marginRight',
        0x10100fa => 'layout_marginBottom',
        0x10100fb => 'listSelector',
        0x10100fc => 'drawSelectorOnTop',
        0x10100fd => 'stackFromBottom',
        0x10100fe => 'scrollingCache',
        0x10100ff => 'textFilterEnabled',
        0x1010100 => 'transcriptMode',
        0x1010101 => 'cacheColorHint',
        0x1010102 => 'dial',
        0x1010103 => 'hand_hour',
        0x1010104 => 'hand_minute',
        0x1010105 => 'format',
        0x1010106 => 'checked',
        0x1010107 => 'button',
        0x1010108 => 'checkMark',
        0x1010109 => 'foreground',
        0x101010a => 'measureAllChildren',
        0x101010b => 'groupIndicator',
        0x101010c => 'childIndicator',
        0x101010d => 'indicatorLeft',
        0x101010e => 'indicatorRight',
        0x101010f => 'childIndicatorLeft',
        0x1010110 => 'childIndicatorRight',
        0x1010111 => 'childDivider',
        0x1010112 => 'animationDuration',
        0x1010113 => 'spacing',
        0x1010114 => 'horizontalSpacing',
        0x1010115 => 'verticalSpacing',
        0x1010116 => 'stretchMode',
        0x1010117 => 'columnWidth',
        0x1010118 => 'numColumns',
        0x1010119 => 'src',
        0x101011a => 'antialias',
        0x101011b => 'filter',
        0x101011c => 'dither',
        0x101011d => 'scaleType',
        0x101011e => 'adjustViewBounds',
        0x101011f => 'maxWidth',
        0x1010120 => 'maxHeight',
        0x1010121 => 'tint',
        0x1010122 => 'baselineAlignBottom',
        0x1010123 => 'cropToPadding',
        0x1010124 => 'textOn',
        0x1010125 => 'textOff',
        0x1010126 => 'baselineAligned',
        0x1010127 => 'baselineAlignedChildIndex',
        0x1010128 => 'weightSum',
        0x1010129 => 'divider',
        0x101012a => 'dividerHeight',
        0x101012b => 'choiceMode',
        0x101012c => 'itemTextAppearance',
        0x101012d => 'horizontalDivider',
        0x101012e => 'verticalDivider',
        0x101012f => 'headerBackground',
        0x1010130 => 'itemBackground',
        0x1010131 => 'itemIconDisabledAlpha',
        0x1010132 => 'rowHeight',
        0x1010133 => 'maxRows',
        0x1010134 => 'maxItemsPerRow',
        0x1010135 => 'moreIcon',
        0x1010136 => 'max',
        0x1010137 => 'progress',
        0x1010138 => 'secondaryProgress',
        0x1010139 => 'indeterminate',
        0x101013a => 'indeterminateOnly',
        0x101013b => 'indeterminateDrawable',
        0x101013c => 'progressDrawable',
        0x101013d => 'indeterminateDuration',
        0x101013e => 'indeterminateBehavior',
        0x101013f => 'minWidth',
        0x1010140 => 'minHeight',
        0x1010141 => 'interpolator',
        0x1010142 => 'thumb',
        0x1010143 => 'thumbOffset',
        0x1010144 => 'numStars',
        0x1010145 => 'rating',
        0x1010146 => 'stepSize',
        0x1010147 => 'isIndicator',
        0x1010148 => 'checkedButton',
        0x1010149 => 'stretchColumns',
        0x101014a => 'shrinkColumns',
        0x101014b => 'collapseColumns',
        0x101014c => 'layout_column',
        0x101014d => 'layout_span',
        0x101014e => 'bufferType',
        0x101014f => 'text',
        0x1010150 => 'hint',
        0x1010151 => 'textScaleX',
        0x1010152 => 'cursorVisible',
        0x1010153 => 'maxLines',
        0x1010154 => 'lines',
        0x1010155 => 'height',
        0x1010156 => 'minLines',
        0x1010157 => 'maxEms',
        0x1010158 => 'ems',
        0x1010159 => 'width',
        0x101015a => 'minEms',
        0x101015b => 'scrollHorizontally',
        0x101015c => 'field public static final deprecated int password',
        0x101015d => 'field public static final deprecated int singleLine',
        0x101015e => 'selectAllOnFocus',
        0x101015f => 'includeFontPadding',
        0x1010160 => 'maxLength',
        0x1010161 => 'shadowColor',
        0x1010162 => 'shadowDx',
        0x1010163 => 'shadowDy',
        0x1010164 => 'shadowRadius',
        0x1010165 => 'field public static final deprecated int numeric',
        0x1010166 => 'digits',
        0x1010167 => 'field public static final deprecated int phoneNumber',
        0x1010168 => 'field public static final deprecated int inputMethod',
        0x1010169 => 'field public static final deprecated int capitalize',
        0x101016a => 'field public static final deprecated int autoText',
        0x101016b => 'field public static final deprecated int editable',
        0x101016c => 'freezesText',
        0x101016d => 'drawableTop',
        0x101016e => 'drawableBottom',
        0x101016f => 'drawableLeft',
        0x1010170 => 'drawableRight',
        0x1010171 => 'drawablePadding',
        0x1010172 => 'completionHint',
        0x1010173 => 'completionHintView',
        0x1010174 => 'completionThreshold',
        0x1010175 => 'dropDownSelector',
        0x1010176 => 'popupBackground',
        0x1010177 => 'inAnimation',
        0x1010178 => 'outAnimation',
        0x1010179 => 'flipInterval',
        0x101017a => 'fillViewport',
        0x101017b => 'prompt',
        0x101017c => 'field public static final deprecated int startYear',
        0x101017d => 'field public static final deprecated int endYear',
        0x101017e => 'mode',
        0x101017f => 'layout_x',
        0x1010180 => 'layout_y',
        0x1010181 => 'layout_weight',
        0x1010182 => 'layout_toLeftOf',
        0x1010183 => 'layout_toRightOf',
        0x1010184 => 'layout_above',
        0x1010185 => 'layout_below',
        0x1010186 => 'layout_alignBaseline',
        0x1010187 => 'layout_alignLeft',
        0x1010188 => 'layout_alignTop',
        0x1010189 => 'layout_alignRight',
        0x101018a => 'layout_alignBottom',
        0x101018b => 'layout_alignParentLeft',
        0x101018c => 'layout_alignParentTop',
        0x101018d => 'layout_alignParentRight',
        0x101018e => 'layout_alignParentBottom',
        0x101018f => 'layout_centerInParent',
        0x1010190 => 'layout_centerHorizontal',
        0x1010191 => 'layout_centerVertical',
        0x1010192 => 'layout_alignWithParentIfMissing',
        0x1010193 => 'layout_scale',
        0x1010194 => 'visible',
        0x1010195 => 'variablePadding',
        0x1010196 => 'constantSize',
        0x1010197 => 'oneshot',
        0x1010198 => 'duration',
        0x1010199 => 'drawable',
        0x101019a => 'shape',
        0x101019b => 'innerRadiusRatio',
        0x101019c => 'thicknessRatio',
        0x101019d => 'startColor',
        0x101019e => 'endColor',
        0x101019f => 'useLevel',
        0x10101a0 => 'angle',
        0x10101a1 => 'type',
        0x10101a2 => 'centerX',
        0x10101a3 => 'centerY',
        0x10101a4 => 'gradientRadius',
        0x10101a5 => 'color',
        0x10101a6 => 'dashWidth',
        0x10101a7 => 'dashGap',
        0x10101a8 => 'radius',
        0x10101a9 => 'topLeftRadius',
        0x10101aa => 'topRightRadius',
        0x10101ab => 'bottomLeftRadius',
        0x10101ac => 'bottomRightRadius',
        0x10101ad => 'left',
        0x10101ae => 'top',
        0x10101af => 'right',
        0x10101b0 => 'bottom',
        0x10101b1 => 'minLevel',
        0x10101b2 => 'maxLevel',
        0x10101b3 => 'fromDegrees',
        0x10101b4 => 'toDegrees',
        0x10101b5 => 'pivotX',
        0x10101b6 => 'pivotY',
        0x10101b7 => 'insetLeft',
        0x10101b8 => 'insetRight',
        0x10101b9 => 'insetTop',
        0x10101ba => 'insetBottom',
        0x10101bb => 'shareInterpolator',
        0x10101bc => 'fillBefore',
        0x10101bd => 'fillAfter',
        0x10101be => 'startOffset',
        0x10101bf => 'repeatCount',
        0x10101c0 => 'repeatMode',
        0x10101c1 => 'zAdjustment',
        0x10101c2 => 'fromXScale',
        0x10101c3 => 'toXScale',
        0x10101c4 => 'fromYScale',
        0x10101c5 => 'toYScale',
        0x10101c6 => 'fromXDelta',
        0x10101c7 => 'toXDelta',
        0x10101c8 => 'fromYDelta',
        0x10101c9 => 'toYDelta',
        0x10101ca => 'fromAlpha',
        0x10101cb => 'toAlpha',
        0x10101cc => 'delay',
        0x10101cd => 'animation',
        0x10101ce => 'animationOrder',
        0x10101cf => 'columnDelay',
        0x10101d0 => 'rowDelay',
        0x10101d1 => 'direction',
        0x10101d2 => 'directionPriority',
        0x10101d3 => 'factor',
        0x10101d4 => 'cycles',
        0x10101d5 => 'searchMode',
        0x10101d6 => 'searchSuggestAuthority',
        0x10101d7 => 'searchSuggestPath',
        0x10101d8 => 'searchSuggestSelection',
        0x10101d9 => 'searchSuggestIntentAction',
        0x10101da => 'searchSuggestIntentData',
        0x10101db => 'queryActionMsg',
        0x10101dc => 'suggestActionMsg',
        0x10101dd => 'suggestActionMsgColumn',
        0x10101de => 'menuCategory',
        0x10101df => 'orderInCategory',
        0x10101e0 => 'checkableBehavior',
        0x10101e1 => 'title',
        0x10101e2 => 'titleCondensed',
        0x10101e3 => 'alphabeticShortcut',
        0x10101e4 => 'numericShortcut',
        0x10101e5 => 'checkable',
        0x10101e6 => 'selectable',
        0x10101e7 => 'orderingFromXml',
        0x10101e8 => 'key',
        0x10101e9 => 'summary',
        0x10101ea => 'order',
        0x10101eb => 'widgetLayout',
        0x10101ec => 'dependency',
        0x10101ed => 'defaultValue',
        0x10101ee => 'shouldDisableView',
        0x10101ef => 'summaryOn',
        0x10101f0 => 'summaryOff',
        0x10101f1 => 'disableDependentsState',
        0x10101f2 => 'dialogTitle',
        0x10101f3 => 'dialogMessage',
        0x10101f4 => 'dialogIcon',
        0x10101f5 => 'positiveButtonText',
        0x10101f6 => 'negativeButtonText',
        0x10101f7 => 'dialogLayout',
        0x10101f8 => 'entryValues',
        0x10101f9 => 'ringtoneType',
        0x10101fa => 'showDefault',
        0x10101fb => 'showSilent',
        0x10101fc => 'scaleWidth',
        0x10101fd => 'scaleHeight',
        0x10101fe => 'scaleGravity',
        0x10101ff => 'ignoreGravity',
        0x1010200 => 'foregroundGravity',
        0x1010201 => 'tileMode',
        0x1010202 => 'targetActivity',
        0x1010203 => 'alwaysRetainTaskState',
        0x1010204 => 'allowTaskReparenting',
        0x1010205 => 'field public static final deprecated int searchButtonText',
        0x1010206 => 'colorForegroundInverse',
        0x1010207 => 'textAppearanceButton',
        0x1010208 => 'listSeparatorTextViewStyle',
        0x1010209 => 'streamType',
        0x101020a => 'clipOrientation',
        0x101020b => 'centerColor',
        0x101020c => 'minSdkVersion',
        0x101020d => 'windowFullscreen',
        0x101020e => 'unselectedAlpha',
        0x101020f => 'progressBarStyleSmallTitle',
        0x1010210 => 'ratingBarStyleIndicator',
        0x1010211 => 'apiKey',
        0x1010212 => 'textColorTertiary',
        0x1010213 => 'textColorTertiaryInverse',
        0x1010214 => 'listDivider',
        0x1010215 => 'soundEffectsEnabled',
        0x1010216 => 'keepScreenOn',
        0x1010217 => 'lineSpacingExtra',
        0x1010218 => 'lineSpacingMultiplier',
        0x1010219 => 'listChoiceIndicatorSingle',
        0x101021a => 'listChoiceIndicatorMultiple',
        0x101021b => 'versionCode',
        0x101021c => 'versionName',
        0x101021d => 'marqueeRepeatLimit',
        0x101021e => 'windowNoDisplay',
        0x101021f => 'backgroundDimEnabled',
        0x1010220 => 'inputType',
        0x1010221 => 'isDefault',
        0x1010222 => 'windowDisablePreview',
        0x1010223 => 'privateImeOptions',
        0x1010224 => 'editorExtras',
        0x1010225 => 'settingsActivity',
        0x1010226 => 'fastScrollEnabled',
        0x1010227 => 'reqTouchScreen',
        0x1010228 => 'reqKeyboardType',
        0x1010229 => 'reqHardKeyboard',
        0x101022a => 'reqNavigation',
        0x101022b => 'windowSoftInputMode',
        0x101022c => 'imeFullscreenBackground',
        0x101022d => 'noHistory',
        0x101022e => 'headerDividersEnabled',
        0x101022f => 'footerDividersEnabled',
        0x1010230 => 'candidatesTextStyleSpans',
        0x1010231 => 'smoothScrollbar',
        0x1010232 => 'reqFiveWayNav',
        0x1010233 => 'keyBackground',
        0x1010234 => 'keyTextSize',
        0x1010235 => 'labelTextSize',
        0x1010236 => 'keyTextColor',
        0x1010237 => 'keyPreviewLayout',
        0x1010238 => 'keyPreviewOffset',
        0x1010239 => 'keyPreviewHeight',
        0x101023a => 'verticalCorrection',
        0x101023b => 'popupLayout',
        0x101023c => 'state_long_pressable',
        0x101023d => 'keyWidth',
        0x101023e => 'keyHeight',
        0x101023f => 'horizontalGap',
        0x1010240 => 'verticalGap',
        0x1010241 => 'rowEdgeFlags',
        0x1010242 => 'codes',
        0x1010243 => 'popupKeyboard',
        0x1010244 => 'popupCharacters',
        0x1010245 => 'keyEdgeFlags',
        0x1010246 => 'isModifier',
        0x1010247 => 'isSticky',
        0x1010248 => 'isRepeatable',
        0x1010249 => 'iconPreview',
        0x101024a => 'keyOutputText',
        0x101024b => 'keyLabel',
        0x101024c => 'keyIcon',
        0x101024d => 'keyboardMode',
        0x101024e => 'isScrollContainer',
        0x101024f => 'fillEnabled',
        0x1010250 => 'updatePeriodMillis',
        0x1010251 => 'initialLayout',
        0x1010252 => 'voiceSearchMode',
        0x1010253 => 'voiceLanguageModel',
        0x1010254 => 'voicePromptText',
        0x1010255 => 'voiceLanguage',
        0x1010256 => 'voiceMaxResults',
        0x1010257 => 'bottomOffset',
        0x1010258 => 'topOffset',
        0x1010259 => 'allowSingleTap',
        0x101025a => 'handle',
        0x101025b => 'content',
        0x101025c => 'animateOnClick',
        0x101025d => 'configure',
        0x101025e => 'hapticFeedbackEnabled',
        0x101025f => 'innerRadius',
        0x1010260 => 'thickness',
        0x1010261 => 'sharedUserLabel',
        0x1010262 => 'dropDownWidth',
        0x1010263 => 'dropDownAnchor',
        0x1010264 => 'imeOptions',
        0x1010265 => 'imeActionLabel',
        0x1010266 => 'imeActionId',
        0x1010268 => 'imeExtractEnterAnimation',
        0x1010269 => 'imeExtractExitAnimation',
        0x101026a => 'tension',
        0x101026b => 'extraTension',
        0x101026c => 'anyDensity',
        0x101026d => 'searchSuggestThreshold',
        0x101026e => 'includeInGlobalSearch',
        0x101026f => 'onClick',
        0x1010270 => 'targetSdkVersion',
        0x1010271 => 'maxSdkVersion',
        0x1010272 => 'testOnly',
        0x1010273 => 'contentDescription',
        0x1010274 => 'gestureStrokeWidth',
        0x1010275 => 'gestureColor',
        0x1010276 => 'uncertainGestureColor',
        0x1010277 => 'fadeOffset',
        0x1010278 => 'fadeDuration',
        0x1010279 => 'gestureStrokeType',
        0x101027a => 'gestureStrokeLengthThreshold',
        0x101027b => 'gestureStrokeSquarenessThreshold',
        0x101027c => 'gestureStrokeAngleThreshold',
        0x101027d => 'eventsInterceptionEnabled',
        0x101027e => 'fadeEnabled',
        0x101027f => 'backupAgent',
        0x1010280 => 'allowBackup',
        0x1010281 => 'glEsVersion',
        0x1010282 => 'queryAfterZeroResults',
        0x1010283 => 'dropDownHeight',
        0x1010284 => 'smallScreens',
        0x1010285 => 'normalScreens',
        0x1010286 => 'largeScreens',
        0x1010287 => 'progressBarStyleInverse',
        0x1010288 => 'progressBarStyleSmallInverse',
        0x1010289 => 'progressBarStyleLargeInverse',
        0x101028a => 'searchSettingsDescription',
        0x101028b => 'textColorPrimaryInverseDisableOnly',
        0x101028c => 'autoUrlDetect',
        0x101028d => 'resizeable',
        0x101028e => 'required',
        0x101028f => 'accountType',
        0x1010290 => 'contentAuthority',
        0x1010291 => 'userVisible',
        0x1010292 => 'windowShowWallpaper',
        0x1010293 => 'wallpaperOpenEnterAnimation',
        0x1010294 => 'wallpaperOpenExitAnimation',
        0x1010295 => 'wallpaperCloseEnterAnimation',
        0x1010296 => 'wallpaperCloseExitAnimation',
        0x1010297 => 'wallpaperIntraOpenEnterAnimation',
        0x1010298 => 'wallpaperIntraOpenExitAnimation',
        0x1010299 => 'wallpaperIntraCloseEnterAnimation',
        0x101029a => 'wallpaperIntraCloseExitAnimation',
        0x101029b => 'supportsUploading',
        0x101029c => 'killAfterRestore',
        0x101029d => 'field public static final deprecated int restoreNeedsApplication',
        0x101029e => 'smallIcon',
        0x101029f => 'accountPreferences',
        0x10102a0 => 'textAppearanceSearchResultSubtitle',
        0x10102a1 => 'textAppearanceSearchResultTitle',
        0x10102a2 => 'summaryColumn',
        0x10102a3 => 'detailColumn',
        0x10102a4 => 'detailSocialSummary',
        0x10102a5 => 'thumbnail',
        0x10102a6 => 'detachWallpaper',
        0x10102a7 => 'finishOnCloseSystemDialogs',
        0x10102a8 => 'scrollbarFadeDuration',
        0x10102a9 => 'scrollbarDefaultDelayBeforeFade',
        0x10102aa => 'fadeScrollbars',
        0x10102ab => 'colorBackgroundCacheHint',
        0x10102ac => 'dropDownHorizontalOffset',
        0x10102ad => 'dropDownVerticalOffset',
        0x10102ae => 'quickContactBadgeStyleWindowSmall',
        0x10102af => 'quickContactBadgeStyleWindowMedium',
        0x10102b0 => 'quickContactBadgeStyleWindowLarge',
        0x10102b1 => 'quickContactBadgeStyleSmallWindowSmall',
        0x10102b2 => 'quickContactBadgeStyleSmallWindowMedium',
        0x10102b3 => 'quickContactBadgeStyleSmallWindowLarge',
        0x10102b4 => 'author',
        0x10102b5 => 'autoStart',
        0x10102b6 => 'expandableListViewWhiteStyle',
        0x10102b7 => 'installLocation',
        0x10102b8 => 'vmSafeMode',
        0x10102b9 => 'webTextViewStyle',
        0x10102ba => 'restoreAnyVersion',
        0x10102bb => 'tabStripLeft',
        0x10102bc => 'tabStripRight',
        0x10102bd => 'tabStripEnabled',
        0x10102be => 'logo',
        0x10102bf => 'xlargeScreens',
        0x10102c0 => 'immersive',
        0x10102c1 => 'overScrollMode',
        0x10102c2 => 'overScrollHeader',
        0x10102c3 => 'overScrollFooter',
        0x10102c4 => 'filterTouchesWhenObscured',
        0x10102c5 => 'textSelectHandleLeft',
        0x10102c6 => 'textSelectHandleRight',
        0x10102c7 => 'textSelectHandle',
        0x10102c8 => 'textSelectHandleWindowStyle',
        0x10102c9 => 'popupAnimationStyle',
        0x10102ca => 'screenSize',
        0x10102cb => 'screenDensity',
        0x10102cc => 'allContactsName',
        0x10102cd => 'windowActionBar',
        0x10102ce => 'actionBarStyle',
        0x10102cf => 'navigationMode',
        0x10102d0 => 'displayOptions',
        0x10102d1 => 'subtitle',
        0x10102d2 => 'customNavigationLayout',
        0x10102d3 => 'hardwareAccelerated',
        0x10102d4 => 'measureWithLargestChild',
        0x10102d5 => 'animateFirstView',
        0x10102d6 => 'dropDownSpinnerStyle',
        0x10102d7 => 'actionDropDownStyle',
        0x10102d8 => 'actionButtonStyle',
        0x10102d9 => 'showAsAction',
        0x10102da => 'previewImage',
        0x10102db => 'actionModeBackground',
        0x10102dc => 'actionModeCloseDrawable',
        0x10102dd => 'windowActionModeOverlay',
        0x10102de => 'valueFrom',
        0x10102df => 'valueTo',
        0x10102e0 => 'valueType',
        0x10102e1 => 'propertyName',
        0x10102e2 => 'ordering',
        0x10102e3 => 'fragment',
        0x10102e4 => 'windowActionBarOverlay',
        0x10102e5 => 'fragmentOpenEnterAnimation',
        0x10102e6 => 'fragmentOpenExitAnimation',
        0x10102e7 => 'fragmentCloseEnterAnimation',
        0x10102e8 => 'fragmentCloseExitAnimation',
        0x10102e9 => 'fragmentFadeEnterAnimation',
        0x10102ea => 'fragmentFadeExitAnimation',
        0x10102eb => 'actionBarSize',
        0x10102ec => 'imeSubtypeLocale',
        0x10102ed => 'imeSubtypeMode',
        0x10102ee => 'imeSubtypeExtraValue',
        0x10102ef => 'splitMotionEvents',
        0x10102f0 => 'listChoiceBackgroundIndicator',
        0x10102f1 => 'spinnerMode',
        0x10102f2 => 'animateLayoutChanges',
        0x10102f3 => 'actionBarTabStyle',
        0x10102f4 => 'actionBarTabBarStyle',
        0x10102f5 => 'actionBarTabTextStyle',
        0x10102f6 => 'actionOverflowButtonStyle',
        0x10102f7 => 'actionModeCloseButtonStyle',
        0x10102f8 => 'titleTextStyle',
        0x10102f9 => 'subtitleTextStyle',
        0x10102fa => 'iconifiedByDefault',
        0x10102fb => 'actionLayout',
        0x10102fc => 'actionViewClass',
        0x10102fd => 'activatedBackgroundIndicator',
        0x10102fe => 'state_activated',
        0x10102ff => 'listPopupWindowStyle',
        0x1010300 => 'popupMenuStyle',
        0x1010301 => 'textAppearanceLargePopupMenu',
        0x1010302 => 'textAppearanceSmallPopupMenu',
        0x1010303 => 'breadCrumbTitle',
        0x1010304 => 'breadCrumbShortTitle',
        0x1010305 => 'listDividerAlertDialog',
        0x1010306 => 'textColorAlertDialogListItem',
        0x1010307 => 'loopViews',
        0x1010308 => 'dialogTheme',
        0x1010309 => 'alertDialogTheme',
        0x101030a => 'dividerVertical',
        0x101030b => 'homeAsUpIndicator',
        0x101030c => 'enterFadeDuration',
        0x101030d => 'exitFadeDuration',
        0x101030e => 'selectableItemBackground',
        0x101030f => 'autoAdvanceViewId',
        0x1010310 => 'useIntrinsicSizeAsMinimum',
        0x1010311 => 'actionModeCutDrawable',
        0x1010312 => 'actionModeCopyDrawable',
        0x1010313 => 'actionModePasteDrawable',
        0x1010314 => 'textEditPasteWindowLayout',
        0x1010315 => 'textEditNoPasteWindowLayout',
        0x1010316 => 'textIsSelectable',
        0x1010317 => 'windowEnableSplitTouch',
        0x1010318 => 'indeterminateProgressStyle',
        0x1010319 => 'progressBarPadding',
        0x101031a => 'field public static final deprecated int animationResolution',
        0x101031b => 'state_accelerated',
        0x101031c => 'baseline',
        0x101031d => 'homeLayout',
        0x101031e => 'opacity',
        0x101031f => 'alpha',
        0x1010320 => 'transformPivotX',
        0x1010321 => 'transformPivotY',
        0x1010322 => 'translationX',
        0x1010323 => 'translationY',
        0x1010324 => 'scaleX',
        0x1010325 => 'scaleY',
        0x1010326 => 'rotation',
        0x1010327 => 'rotationX',
        0x1010328 => 'rotationY',
        0x1010329 => 'showDividers',
        0x101032a => 'dividerPadding',
        0x101032b => 'borderlessButtonStyle',
        0x101032c => 'dividerHorizontal',
        0x101032d => 'itemPadding',
        0x101032e => 'buttonBarStyle',
        0x101032f => 'buttonBarButtonStyle',
        0x1010330 => 'segmentedButtonStyle',
        0x1010331 => 'staticWallpaperPreview',
        0x1010332 => 'allowParallelSyncs',
        0x1010333 => 'isAlwaysSyncable',
        0x1010334 => 'verticalScrollbarPosition',
        0x1010335 => 'fastScrollAlwaysVisible',
        0x1010336 => 'fastScrollThumbDrawable',
        0x1010337 => 'fastScrollPreviewBackgroundLeft',
        0x1010338 => 'fastScrollPreviewBackgroundRight',
        0x1010339 => 'fastScrollTrackDrawable',
        0x101033a => 'fastScrollOverlayPosition',
        0x101033b => 'customTokens',
        0x101033c => 'nextFocusForward',
        0x101033d => 'firstDayOfWeek',
        0x101033e => 'showWeekNumber',
        0x101033f => 'minDate',
        0x1010340 => 'maxDate',
        0x1010341 => 'shownWeekCount',
        0x1010342 => 'selectedWeekBackgroundColor',
        0x1010343 => 'focusedMonthDateColor',
        0x1010344 => 'unfocusedMonthDateColor',
        0x1010345 => 'weekNumberColor',
        0x1010346 => 'weekSeparatorLineColor',
        0x1010347 => 'selectedDateVerticalBar',
        0x1010348 => 'weekDayTextAppearance',
        0x1010349 => 'dateTextAppearance',
        0x101034b => 'spinnersShown',
        0x101034c => 'calendarViewShown',
        0x101034d => 'state_multiline',
        0x101034e => 'detailsElementBackground',
        0x101034f => 'textColorHighlightInverse',
        0x1010350 => 'textColorLinkInverse',
        0x1010351 => 'editTextColor',
        0x1010352 => 'editTextBackground',
        0x1010353 => 'horizontalScrollViewStyle',
        0x1010354 => 'layerType',
        0x1010355 => 'alertDialogIcon',
        0x1010356 => 'windowMinWidthMajor',
        0x1010357 => 'windowMinWidthMinor',
        0x1010358 => 'queryHint',
        0x1010359 => 'fastScrollTextColor',
        0x101035a => 'largeHeap',
        0x101035b => 'windowCloseOnTouchOutside',
        0x101035c => 'datePickerStyle',
        0x101035d => 'calendarViewStyle',
        0x101035e => 'textEditSidePasteWindowLayout',
        0x101035f => 'textEditSideNoPasteWindowLayout',
        0x1010360 => 'actionMenuTextAppearance',
        0x1010361 => 'actionMenuTextColor',
        0x1010362 => 'textCursorDrawable',
        0x1010363 => 'resizeMode',
        0x1010364 => 'requiresSmallestWidthDp',
        0x1010365 => 'compatibleWidthLimitDp',
        0x1010366 => 'largestWidthLimitDp',
        0x1010367 => 'state_hovered',
        0x1010368 => 'state_drag_can_accept',
        0x1010369 => 'state_drag_hovered',
        0x101036a => 'stopWithTask',
        0x101036b => 'switchTextOn',
        0x101036c => 'switchTextOff',
        0x101036d => 'switchPreferenceStyle',
        0x101036e => 'switchTextAppearance',
        0x101036f => 'track',
        0x1010370 => 'switchMinWidth',
        0x1010371 => 'switchPadding',
        0x1010372 => 'thumbTextPadding',
        0x1010373 => 'textSuggestionsWindowStyle',
        0x1010374 => 'textEditSuggestionItemLayout',
        0x1010375 => 'rowCount',
        0x1010376 => 'rowOrderPreserved',
        0x1010377 => 'columnCount',
        0x1010378 => 'columnOrderPreserved',
        0x1010379 => 'useDefaultMargins',
        0x101037a => 'alignmentMode',
        0x101037b => 'layout_row',
        0x101037c => 'layout_rowSpan',
        0x101037d => 'layout_columnSpan',
        0x101037e => 'actionModeSelectAllDrawable',
        0x101037f => 'isAuxiliary',
        0x1010380 => 'accessibilityEventTypes',
        0x1010381 => 'packageNames',
        0x1010382 => 'accessibilityFeedbackType',
        0x1010383 => 'notificationTimeout',
        0x1010384 => 'accessibilityFlags',
        0x1010385 => 'canRetrieveWindowContent',
        0x1010386 => 'listPreferredItemHeightLarge',
        0x1010387 => 'listPreferredItemHeightSmall',
        0x1010388 => 'actionBarSplitStyle',
        0x1010389 => 'actionProviderClass',
        0x101038a => 'backgroundStacked',
        0x101038b => 'backgroundSplit',
        0x101038c => 'textAllCaps',
        0x101038d => 'colorPressedHighlight',
        0x101038e => 'colorLongPressedHighlight',
        0x101038f => 'colorFocusedHighlight',
        0x1010390 => 'colorActivatedHighlight',
        0x1010391 => 'colorMultiSelectHighlight',
        0x1010392 => 'drawableStart',
        0x1010393 => 'drawableEnd',
        0x1010394 => 'actionModeStyle',
        0x1010395 => 'minResizeWidth',
        0x1010396 => 'minResizeHeight',
        0x1010397 => 'actionBarWidgetTheme',
        0x1010398 => 'uiOptions',
        0x1010399 => 'subtypeLocale',
        0x101039a => 'subtypeExtraValue',
        0x101039b => 'actionBarDivider',
        0x101039c => 'actionBarItemBackground',
        0x101039d => 'actionModeSplitBackground',
        0x101039e => 'textAppearanceListItem',
        0x101039f => 'textAppearanceListItemSmall',
        0x10103a0 => 'targetDescriptions',
        0x10103a1 => 'directionDescriptions',
        0x10103a2 => 'overridesImplicitlyEnabledSubtype',
        0x10103a3 => 'listPreferredItemPaddingLeft',
        0x10103a4 => 'listPreferredItemPaddingRight',
        0x10103a5 => 'requiresFadingEdge',
        0x10103a6 => 'publicKey',
        0x10103a7 => 'parentActivityName',
        0x10103a9 => 'isolatedProcess',
        0x10103aa => 'importantForAccessibility',
        0x10103ab => 'keyboardLayout',
        0x10103ac => 'fontFamily',
        0x10103ad => 'mediaRouteButtonStyle',
        0x10103ae => 'mediaRouteTypes',
        0x10103af => 'supportsRtl',
        0x10103b0 => 'textDirection',
        0x10103b1 => 'textAlignment',
        0x10103b2 => 'layoutDirection',
        0x10103b3 => 'paddingStart',
        0x10103b4 => 'paddingEnd',
        0x10103b5 => 'layout_marginStart',
        0x10103b6 => 'layout_marginEnd',
        0x10103b7 => 'layout_toStartOf',
        0x10103b8 => 'layout_toEndOf',
        0x10103b9 => 'layout_alignStart',
        0x10103ba => 'layout_alignEnd',
        0x10103bb => 'layout_alignParentStart',
        0x10103bc => 'layout_alignParentEnd',
        0x10103bd => 'listPreferredItemPaddingStart',
        0x10103be => 'listPreferredItemPaddingEnd',
        0x10103bf => 'singleUser',
        0x10103c0 => 'presentationTheme',
        0x10103c1 => 'subtypeId',
        0x10103c2 => 'initialKeyguardLayout',
        0x10103c4 => 'widgetCategory',
        0x10103c5 => 'permissionGroupFlags',
        0x10103c6 => 'labelFor',
        0x10103c7 => 'permissionFlags',
        0x10103c8 => 'checkedTextViewStyle',
        0x10103c9 => 'showOnLockScreen',
        0x10103ca => 'format12Hour',
        0x10103cb => 'format24Hour',
        0x10103cc => 'timeZone',
    ];

    /**
     * @param Stream $apkStream
     */
    public function __construct(Stream $apkStream)
    {
        $this->bytes = $apkStream->getByteArray();
    }

    /**
     * @param $file
     * @param null $destination
     * @throws \Exception
     */
    public static function decompressFile($file, $destination = null)
    {
        if (!is_file($file)) {
            throw new \Exception("{$file} is not a regular file");
        }

        $parser = new self(new Stream(fopen($file, 'rd')));
        //TODO : write a method in this class, ->saveToFile();
        file_put_contents($destination === null ? $file : $destination, $parser->getXmlString());
    }

    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function getXmlString()
    {
        if (!$this->ready) {
            $this->decompress();
        }

        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $this->xml);
    }

    /**
     * @throws \Exception
     */
    public function decompress()
    {
        $headerSize = $this->littleEndianShort($this->bytes, 1 * 2);
        $dataSize = $this->littleEndianWord($this->bytes, 2 * 2);

        $off = $headerSize;
        $resIdsOffset = -1;
        $resIdsCount = 0;


        while ($off < ($dataSize - 8)) {
            $chunkType = $this->littleEndianShort($this->bytes, $off + 0 * 2);
            $chunkHeaderSize = $this->littleEndianShort($this->bytes, $off + 1 * 2);
            $chunkSize = $this->littleEndianWord($this->bytes, $off + 2 * 2);
            if ($off + $chunkSize > $dataSize) {
                break;
            }           // not a chunk

            switch ($chunkType) {
                case self::RES_STRING_POOL_TYPE:
                    $numbStrings = $this->littleEndianWord($this->bytes, $off + 8);
                    $flags = $this->littleEndianWord($this->bytes, $off + 8 * 2);
                    $this->isUTF8 = ($flags & self::UTF8_FLAG) != 0;
                    $sitOff = $off + $chunkHeaderSize;
                    $stOff = $sitOff + $numbStrings * 4;
                    break;
                case  self::RES_XML_RESOURCE_MAP_TYPE:
                    $resIdsOffset = $off + $chunkHeaderSize;
                    $resIdsCount = ($chunkSize - $chunkHeaderSize) / 4;
                    break;
                case self::RES_XML_START_ELEMENT_TYPE:
                    // xml starts here.
                    break 2;
                    break;

                case self::RES_NULL_TYPE:
                    // read null header.
                    break;
            }

            $off += $chunkSize;
        }

        $indentCount = 0;
        $startTagLineNo = -2;

        while ($off < count($this->bytes)) {
            $currentTag = $this->littleEndianShort($this->bytes, $off); // begin
            $lineNo = $this->littleEndianWord($this->bytes, $off + 2 * 4); // itemtype
            $nameNsSi = $this->littleEndianWord($this->bytes, $off + 4 * 4); //headersize
            $nameSi = $this->littleEndianWord($this->bytes, $off + 5 * 4); //itembodysize

            switch ($currentTag) {
                case self::TAG_NULL:
                    $off += 4;
                    break;

                case self::TAG_START:
                    {
                        $tagSix = $this->littleEndianWord($this->bytes, $off + 6 * 4);
                        $numbAttrs = $this->littleEndianWord($this->bytes, $off + 7 * 4);
                        $off += 9 * 4;
                        $tagName = $this->compXmlString($this->bytes, $sitOff, $stOff, $nameSi);
                        $startTagLineNo = $lineNo;
                        $attr_string = "";

                        for ($ii = 0; $ii < $numbAttrs; $ii++) {
                            $attrNameNsSi = $this->littleEndianWord($this->bytes, $off);
                            $attrNameSi = $this->littleEndianWord($this->bytes, $off + 1 * 4);
                            $attrValueSi = $this->littleEndianWord($this->bytes, $off + 2 * 4);
                            $attrFlags = $this->littleEndianWord($this->bytes, $off + 3 * 4);
                            $attrResId = $this->littleEndianWord($this->bytes, $off + 4 * 4);
                            $off += 5 * 4;

                            $attrName = $this->compXmlString($this->bytes, $sitOff, $stOff, $attrNameSi);
                            $attrNameResID = $this->littleEndianWord($this->bytes, $resIdsOffset + ($attrNameSi * 4));
                            if (empty($attrName)) {
                                $attrName = $this->getResourceNameFromID($attrNameResID);
                            }


                            //-1 for 32bit PHP
                            //maybe will be better "if (dechex($attrValueSi) != 'ffffffff') {" ?
                            if (($attrValueSi != 0xffffffff) && ($attrValueSi != -1)) {
                                $attrValue = $this->compXmlString($this->bytes, $sitOff, $stOff, $attrValueSi);
                            } else {
                                $attrValue = "0x" . dechex($attrResId);
                            }

                            $attr_string .= " " . $attrName . "=\"" . htmlspecialchars($attrValue) . "\"";
                        }

                        $this->appendXmlIndent($indentCount, "<" . $tagName . $attr_string . ">");
                        $indentCount++;
                    }
                    break;

                case self::TAG_END:
                    {
                        $indentCount--;
                        $off += 6 * 4;
                        $tagName = $this->compXmlString($this->bytes, $sitOff, $stOff, $nameSi);
                        $this->appendXmlIndent($indentCount, "</" . $tagName . ">");
                    }
                    break;

                case self::TAG_DOC_START:
                    {
                        $off += 6 * 4;
                        $this->nsNum++;
                    }
                    break;
                case self::TAG_DOC_END:
                    {
                        if ($this->nsNum == 0) {
                            $this->ready = true;
                            break 2;
                        }
                        $this->nsNum--;
                        $off += 6 * 4;
                    }
                    break;

                case self::TAG_TEXT:
                    {
                        // The text tag appears to be used when Android references an id value that is not
                        // a string literal
                        // To skip it, read forward until finding the sentinal 0x00000000 after finding
                        // the sentinal 0xffffffff

                        $sentinal = -1;
                        while ($off < count($this->bytes)) {
                            $curr = $this->littleEndianWord($this->bytes, $off);

                            $off += 4;
                            if ($off > count($this->bytes)) {
                                throw new \Exception("Sentinal not found before end of file");
                            }
                            if ($curr == $sentinal && $sentinal == -1) {
                                $sentinal = 0;
                            } else {
                                if ($curr == $sentinal) {
                                    break;
                                }
                            }
                        }
                    }
                    break;

                default:
                    throw new \Exception("Unrecognized tag code '" . dechex($currentTag) . "' at offset " . $off);
                    break;
            }
        }
    }

    /**
     * @param $arr
     * @param $off
     * @return int
     */
    public function littleEndianShort($arr, $off)
    {
        $signShifAmount = (PHP_INT_SIZE - 2) << 3; // the amount of bits to shift back and forth, so that we get the correct signage
        return (($arr[$off + 1] << 8 & 0xff00 | $arr[$off] & 0xFF) << $signShifAmount) >> $signShifAmount;
    }

    /**
     * @param $arr
     * @param $off
     * @return int
     */
    public function littleEndianWord($arr, $off)
    {
        $signShifAmount = (PHP_INT_SIZE - 4) << 3; // the amount of bits to shift back and forth, so that we get the correct signage
        return (($arr[$off + 3] << 24 & 0xff000000 | $arr[$off + 2] << 16 & 0xff0000 | $arr[$off + 1] << 8 & 0xff00 | $arr[$off] & 0xFF) << $signShifAmount) >> $signShifAmount;
    }

    /**
     * @param $xml
     * @param $sitOff
     * @param $stOff
     * @param $str_index
     * @return null|string
     */
    public function compXmlString($xml, $sitOff, $stOff, $str_index)
    {
        if ($str_index < 0) {
            return null;
        }

        $strOff = $stOff + $this->littleEndianWord($xml, $sitOff + $str_index * 4);
        return $this->isUTF8 ? $this->compXmlUTF8StringAt($xml, $strOff) : $this->compXmlUTF16StringAt($xml, $strOff);
    }

    /**
     * @param $arr
     * @param $string_offset
     * @return string
     */
    public function compXmlUTF8StringAt($arr, $string_offset)
    {
        $val = $arr[$string_offset];
        // We skip the utf16 length of the string
        $string_offset += ($val & 0x80) != 0 ? 2 : 1;
        // And we read only the utf-8 encoded length of the string
        $val = $arr[$string_offset];
        $string_offset += 1;
        if (($val & 0x80) != 0) {
            $low = ($arr[$string_offset] & 0xFF);
            $length = (($val & 0x7F) << 8) + $low;
            $string_offset += 1;
        } else {
            $length = $val;
        }

        $strEnd = $string_offset + ($length);
        $string = "";
        for ($i = $string_offset; $i < $strEnd; $i++) {
            $string .= chr($arr[$i]);
        }

        return $string;
    }

    /**
     * @param $arr
     * @param $string_offset
     * @return string
     */
    public function compXmlUTF16StringAt($arr, $string_offset)
    {
        $strlen = $arr[$string_offset + 1] << 8 & 0xff00 | $arr[$string_offset] & 0xff;
        $string_offset += 2;
        $string = "";

        // We are dealing with Unicode strings, so each char is 2 bytes
        $strEnd = $string_offset + ($strlen * 2);
        if (function_exists("mb_convert_encoding")) {
            for ($i = $string_offset; $i < $strEnd; $i++) {
                $string .= chr($arr[$i]);
            }
            $string = mb_convert_encoding($string, "UTF-8", "UTF-16LE");
        } else {  // sonvert as ascii, skipping every second char
            for ($i = $string_offset; $i < $strEnd; $i += 2) {
                $string .= chr($arr[$i]);
            }
        }
        return $string;
    }

    public function getResourceNameFromID($id)
    {
        return self::$resourceMap[$id] ?? "0x" . dechex($id);
    }

    public function appendXmlIndent($indent, $str)
    {
        $this->appendXml(substr(self::$indent_spaces, 0, min($indent * 2, strlen(self::$indent_spaces))) . $str);
    }

    /**
     * @param $str
     */
    public function appendXml($str)
    {
        $this->xml .= $str . "\r\n";
    }

    /**
     * Print XML content
     * @throws \Exception
     */
    public function output()
    {
        echo $this->getXmlString();
    }

    /**
     * @param string $className
     * @return \SimpleXMLElement
     * @throws XmlParserException
     * @throws \Exception
     */
    public function getXmlObject($className = '\SimpleXmlElement')
    {
        if ($this->xmlObject === null || !$this->xmlObject instanceof $className) {
            $prev = libxml_use_internal_errors(true);
            $xml = $this->getXmlString();
            $this->xmlObject = simplexml_load_string($xml, $className);
            if ($this->xmlObject === false) {
                throw new XmlParserException($xml);
            }
            libxml_use_internal_errors($prev);
        }

        return $this->xmlObject;
    }
}
