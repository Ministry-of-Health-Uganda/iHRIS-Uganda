<?php namespace FHIR_DSTU_TWO\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: May 13th, 2016
 * 
 * PHPFHIR Copyright:
 * 
 * Copyright 2016 Daniel Carbone (daniel.p.carbone@gmail.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *        http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 * 
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 * 
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 * 
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 * 
 * 
 *   Generated on Sat, Oct 24, 2015 07:41+1100 for FHIR v1.0.2
 * 
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 * 
 */

use FHIR_DSTU_TWO\FHIRResource\FHIRDomainResource;
use FHIR_DSTU_TWO\JsonSerializable;

/**
 * A homogeneous material with a definite composition.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRSubstance extends FHIRDomainResource implements JsonSerializable
{
    /**
     * Unique identifier for the substance.
     * @var \FHIR_DSTU_TWO\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = array();

    /**
     * A code that classifies the general type of substance.  This is used  for searching, sorting and display purposes.
     * @var \FHIR_DSTU_TWO\FHIRElement\FHIRCodeableConcept[]
     */
    public $category = array();

    /**
     * A code (or set of codes) that identify this substance.
     * @var \FHIR_DSTU_TWO\FHIRElement\FHIRCodeableConcept
     */
    public $code = null;

    /**
     * A description of the substance - its appearance, handling requirements, and other usage notes.
     * @var \FHIR_DSTU_TWO\FHIRElement\FHIRString
     */
    public $description = null;

    /**
     * Substance may be used to describe a kind of substance, or a specific package/container of the substance: an instance.
     * @var \FHIR_DSTU_TWO\FHIRResource\FHIRSubstance\FHIRSubstanceInstance[]
     */
    public $instance = array();

    /**
     * A substance can be composed of other substances.
     * @var \FHIR_DSTU_TWO\FHIRResource\FHIRSubstance\FHIRSubstanceIngredient[]
     */
    public $ingredient = array();

    /**
     * @var string
     */
    private $_fhirElementName = 'Substance';

    /**
     * Unique identifier for the substance.
     * @return \FHIR_DSTU_TWO\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Unique identifier for the substance.
     * @param \FHIR_DSTU_TWO\FHIRElement\FHIRIdentifier[] $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * A code that classifies the general type of substance.  This is used  for searching, sorting and display purposes.
     * @return \FHIR_DSTU_TWO\FHIRElement\FHIRCodeableConcept[]
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * A code that classifies the general type of substance.  This is used  for searching, sorting and display purposes.
     * @param \FHIR_DSTU_TWO\FHIRElement\FHIRCodeableConcept[] $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->category[] = $category;
        return $this;
    }

    /**
     * A code (or set of codes) that identify this substance.
     * @return \FHIR_DSTU_TWO\FHIRElement\FHIRCodeableConcept
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A code (or set of codes) that identify this substance.
     * @param \FHIR_DSTU_TWO\FHIRElement\FHIRCodeableConcept $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * A description of the substance - its appearance, handling requirements, and other usage notes.
     * @return \FHIR_DSTU_TWO\FHIRElement\FHIRString
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A description of the substance - its appearance, handling requirements, and other usage notes.
     * @param \FHIR_DSTU_TWO\FHIRElement\FHIRString $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Substance may be used to describe a kind of substance, or a specific package/container of the substance: an instance.
     * @return \FHIR_DSTU_TWO\FHIRResource\FHIRSubstance\FHIRSubstanceInstance[]
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Substance may be used to describe a kind of substance, or a specific package/container of the substance: an instance.
     * @param \FHIR_DSTU_TWO\FHIRResource\FHIRSubstance\FHIRSubstanceInstance[] $instance
     * @return $this
     */
    public function addInstance($instance)
    {
        $this->instance[] = $instance;
        return $this;
    }

    /**
     * A substance can be composed of other substances.
     * @return \FHIR_DSTU_TWO\FHIRResource\FHIRSubstance\FHIRSubstanceIngredient[]
     */
    public function getIngredient()
    {
        return $this->ingredient;
    }

    /**
     * A substance can be composed of other substances.
     * @param \FHIR_DSTU_TWO\FHIRResource\FHIRSubstance\FHIRSubstanceIngredient[] $ingredient
     * @return $this
     */
    public function addIngredient($ingredient)
    {
        $this->ingredient[] = $ingredient;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->identifier)) {
            $json['identifier'] = array();
            foreach($this->identifier as $identifier) {
                $json['identifier'][] = $identifier->jsonSerialize();
            }
        }
        if (0 < count($this->category)) {
            $json['category'] = array();
            foreach($this->category as $category) {
                $json['category'][] = $category->jsonSerialize();
            }
        }
        if (null !== $this->code) $json['code'] = $this->code->jsonSerialize();
        if (null !== $this->description) $json['description'] = $this->description->jsonSerialize();
        if (0 < count($this->instance)) {
            $json['instance'] = array();
            foreach($this->instance as $instance) {
                $json['instance'][] = $instance->jsonSerialize();
            }
        }
        if (0 < count($this->ingredient)) {
            $json['ingredient'] = array();
            foreach($this->ingredient as $ingredient) {
                $json['ingredient'][] = $ingredient->jsonSerialize();
            }
        }
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) $sxe = new \SimpleXMLElement('<Substance xmlns="http://hl7.org/fhir"></Substance>');
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (0 < count($this->category)) {
            foreach($this->category as $category) {
                $category->xmlSerialize(true, $sxe->addChild('category'));
            }
        }
        if (null !== $this->code) $this->code->xmlSerialize(true, $sxe->addChild('code'));
        if (null !== $this->description) $this->description->xmlSerialize(true, $sxe->addChild('description'));
        if (0 < count($this->instance)) {
            foreach($this->instance as $instance) {
                $instance->xmlSerialize(true, $sxe->addChild('instance'));
            }
        }
        if (0 < count($this->ingredient)) {
            foreach($this->ingredient as $ingredient) {
                $ingredient->xmlSerialize(true, $sxe->addChild('ingredient'));
            }
        }
        if ($returnSXE) return $sxe;
        return $sxe->saveXML();
    }


}