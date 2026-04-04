<?php

namespace Webkul\SellerSubAccount\Block\Widget;

class Dob extends \Magento\Customer\Block\Widget\Dob
{
    /**
     * Get Field Html
     *
     * @return mixed
     */
    public function getFieldHtml()
    {
        $extraParams = $this->getHtmlExtraParams();
        $image = $this->getViewFileUrl('Magento_Theme::calendar.png');
        $this->dateElement->setData(
            [
                'extra_params' => $extraParams,
                'name' => $this->getHtmlId(),
                'id' => $this->getHtmlId(),
                'image' => $image,
                'years_range' => '-120y:c+nn',
                'max_date' => '-1d',
                'change_month' => 'true',
                'change_year' => 'true',
                'show_on' => 'both',
                'class' => $this->getHtmlClass(),
                'value' => $this->getValue(),
                'date_format' => $this->getDateFormat(),
                'first_day' => $this->getFirstDay()
            ]
        );
        return $this->dateElement->getHtml();
    }

    /**
     * Get Html Extra Params
     *
     * @return string
     */
    public function getHtmlExtraParams()
    {
        /* NEW LINES */
        $firstDateLetters = substr(strtolower($this->getDateFormat()), 0, 1);
        if ($firstDateLetters == 'm') {
            $ruleName = 'validate-date'; /* Rule for mm/dd/yyyy date format */
        } else {
            $ruleName = 'validate-date-au'; /* Rule for dd/mm/yyyy date format */
        }
        /* END NEW LINES */
        $extraParams = [
            "'".$ruleName."':true" /* MODIFIED LINE */
        ];
      
        if ($this->isRequired()) {
            $extraParams[] = 'required:true';
        }

        $extraParams = implode(', ', $extraParams);

        return 'data-validate="{' . $extraParams . '}"';
    }
    
    /**
     * Get Date Format
     *
     * @return string
     */
    public function getDateFormat()
    {
        $dateFormate = $this->_localeDate->getDateFormatWithLongYear();
        /** Escape RTL characters which are present in some locales and corrupt formatting */
        $escapedDateFormat = preg_replace('/[^MmDdYy\/\.\-]/', '', $dateFormate);

        return $escapedDateFormat;
    }
}
