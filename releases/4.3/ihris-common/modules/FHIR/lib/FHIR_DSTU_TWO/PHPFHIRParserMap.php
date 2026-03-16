<?php namespace FHIR_DSTU_TWO;

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
 */

class PHPFHIRParserMap implements \ArrayAccess, \Iterator
{
    /** @var array */
    private $_bigDumbMap = array (
  'markdown' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'markdown-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'integer' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'integer-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'dateTime' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'dateTime-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'unsignedInt' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'unsignedInt-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'code' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'code-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'date' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'date-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'decimal' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'uri' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'uri-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'id' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'base64Binary' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'base64Binary-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'time' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'time-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'oid' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'oid-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'positiveInt' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'positiveInt-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'string' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'string-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'boolean' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'boolean-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'uuid' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRUuid',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'uuid-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'instant' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'instant-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'ResourceContainer' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResourceContainer',
    'extendedElementName' => NULL,
    'properties' => 
    array (
      'Account' => 
      array (
        'setter' => 'setAccount',
        'element' => 'Account',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRAccount',
      ),
      'AllergyIntolerance' => 
      array (
        'setter' => 'setAllergyIntolerance',
        'element' => 'AllergyIntolerance',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRAllergyIntolerance',
      ),
      'Appointment' => 
      array (
        'setter' => 'setAppointment',
        'element' => 'Appointment',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRAppointment',
      ),
      'AppointmentResponse' => 
      array (
        'setter' => 'setAppointmentResponse',
        'element' => 'AppointmentResponse',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRAppointmentResponse',
      ),
      'AuditEvent' => 
      array (
        'setter' => 'setAuditEvent',
        'element' => 'AuditEvent',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRAuditEvent',
      ),
      'Basic' => 
      array (
        'setter' => 'setBasic',
        'element' => 'Basic',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRBasic',
      ),
      'Binary' => 
      array (
        'setter' => 'setBinary',
        'element' => 'Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRBinary',
      ),
      'BodySite' => 
      array (
        'setter' => 'setBodySite',
        'element' => 'BodySite',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRBodySite',
      ),
      'Bundle' => 
      array (
        'setter' => 'setBundle',
        'element' => 'Bundle',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle',
      ),
      'CarePlan' => 
      array (
        'setter' => 'setCarePlan',
        'element' => 'CarePlan',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRCarePlan',
      ),
      'Claim' => 
      array (
        'setter' => 'setClaim',
        'element' => 'Claim',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRClaim',
      ),
      'ClaimResponse' => 
      array (
        'setter' => 'setClaimResponse',
        'element' => 'ClaimResponse',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRClaimResponse',
      ),
      'ClinicalImpression' => 
      array (
        'setter' => 'setClinicalImpression',
        'element' => 'ClinicalImpression',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRClinicalImpression',
      ),
      'Communication' => 
      array (
        'setter' => 'setCommunication',
        'element' => 'Communication',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRCommunication',
      ),
      'CommunicationRequest' => 
      array (
        'setter' => 'setCommunicationRequest',
        'element' => 'CommunicationRequest',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRCommunicationRequest',
      ),
      'Composition' => 
      array (
        'setter' => 'setComposition',
        'element' => 'Composition',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRComposition',
      ),
      'ConceptMap' => 
      array (
        'setter' => 'setConceptMap',
        'element' => 'ConceptMap',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRConceptMap',
      ),
      'Condition' => 
      array (
        'setter' => 'setCondition',
        'element' => 'Condition',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRCondition',
      ),
      'Conformance' => 
      array (
        'setter' => 'setConformance',
        'element' => 'Conformance',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRConformance',
      ),
      'Contract' => 
      array (
        'setter' => 'setContract',
        'element' => 'Contract',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRContract',
      ),
      'Coverage' => 
      array (
        'setter' => 'setCoverage',
        'element' => 'Coverage',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRCoverage',
      ),
      'DataElement' => 
      array (
        'setter' => 'setDataElement',
        'element' => 'DataElement',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDataElement',
      ),
      'DetectedIssue' => 
      array (
        'setter' => 'setDetectedIssue',
        'element' => 'DetectedIssue',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDetectedIssue',
      ),
      'Device' => 
      array (
        'setter' => 'setDevice',
        'element' => 'Device',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDevice',
      ),
      'DeviceComponent' => 
      array (
        'setter' => 'setDeviceComponent',
        'element' => 'DeviceComponent',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDeviceComponent',
      ),
      'DeviceMetric' => 
      array (
        'setter' => 'setDeviceMetric',
        'element' => 'DeviceMetric',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDeviceMetric',
      ),
      'DeviceUseRequest' => 
      array (
        'setter' => 'setDeviceUseRequest',
        'element' => 'DeviceUseRequest',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDeviceUseRequest',
      ),
      'DeviceUseStatement' => 
      array (
        'setter' => 'setDeviceUseStatement',
        'element' => 'DeviceUseStatement',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDeviceUseStatement',
      ),
      'DiagnosticOrder' => 
      array (
        'setter' => 'setDiagnosticOrder',
        'element' => 'DiagnosticOrder',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDiagnosticOrder',
      ),
      'DiagnosticReport' => 
      array (
        'setter' => 'setDiagnosticReport',
        'element' => 'DiagnosticReport',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDiagnosticReport',
      ),
      'DocumentManifest' => 
      array (
        'setter' => 'setDocumentManifest',
        'element' => 'DocumentManifest',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDocumentManifest',
      ),
      'DocumentReference' => 
      array (
        'setter' => 'setDocumentReference',
        'element' => 'DocumentReference',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDocumentReference',
      ),
      'EligibilityRequest' => 
      array (
        'setter' => 'setEligibilityRequest',
        'element' => 'EligibilityRequest',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREligibilityRequest',
      ),
      'EligibilityResponse' => 
      array (
        'setter' => 'setEligibilityResponse',
        'element' => 'EligibilityResponse',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREligibilityResponse',
      ),
      'Encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Encounter',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREncounter',
      ),
      'EnrollmentRequest' => 
      array (
        'setter' => 'setEnrollmentRequest',
        'element' => 'EnrollmentRequest',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREnrollmentRequest',
      ),
      'EnrollmentResponse' => 
      array (
        'setter' => 'setEnrollmentResponse',
        'element' => 'EnrollmentResponse',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREnrollmentResponse',
      ),
      'EpisodeOfCare' => 
      array (
        'setter' => 'setEpisodeOfCare',
        'element' => 'EpisodeOfCare',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREpisodeOfCare',
      ),
      'ExplanationOfBenefit' => 
      array (
        'setter' => 'setExplanationOfBenefit',
        'element' => 'ExplanationOfBenefit',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRExplanationOfBenefit',
      ),
      'FamilyMemberHistory' => 
      array (
        'setter' => 'setFamilyMemberHistory',
        'element' => 'FamilyMemberHistory',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRFamilyMemberHistory',
      ),
      'Flag' => 
      array (
        'setter' => 'setFlag',
        'element' => 'Flag',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRFlag',
      ),
      'Goal' => 
      array (
        'setter' => 'setGoal',
        'element' => 'Goal',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRGoal',
      ),
      'Group' => 
      array (
        'setter' => 'setGroup',
        'element' => 'Group',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRGroup',
      ),
      'HealthcareService' => 
      array (
        'setter' => 'setHealthcareService',
        'element' => 'HealthcareService',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRHealthcareService',
      ),
      'ImagingObjectSelection' => 
      array (
        'setter' => 'setImagingObjectSelection',
        'element' => 'ImagingObjectSelection',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRImagingObjectSelection',
      ),
      'ImagingStudy' => 
      array (
        'setter' => 'setImagingStudy',
        'element' => 'ImagingStudy',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRImagingStudy',
      ),
      'Immunization' => 
      array (
        'setter' => 'setImmunization',
        'element' => 'Immunization',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRImmunization',
      ),
      'ImmunizationRecommendation' => 
      array (
        'setter' => 'setImmunizationRecommendation',
        'element' => 'ImmunizationRecommendation',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRImmunizationRecommendation',
      ),
      'ImplementationGuide' => 
      array (
        'setter' => 'setImplementationGuide',
        'element' => 'ImplementationGuide',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRImplementationGuide',
      ),
      'List' => 
      array (
        'setter' => 'setList',
        'element' => 'List',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRList',
      ),
      'Location' => 
      array (
        'setter' => 'setLocation',
        'element' => 'Location',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRLocation',
      ),
      'Media' => 
      array (
        'setter' => 'setMedia',
        'element' => 'Media',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedia',
      ),
      'Medication' => 
      array (
        'setter' => 'setMedication',
        'element' => 'Medication',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedication',
      ),
      'MedicationAdministration' => 
      array (
        'setter' => 'setMedicationAdministration',
        'element' => 'MedicationAdministration',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedicationAdministration',
      ),
      'MedicationDispense' => 
      array (
        'setter' => 'setMedicationDispense',
        'element' => 'MedicationDispense',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedicationDispense',
      ),
      'MedicationOrder' => 
      array (
        'setter' => 'setMedicationOrder',
        'element' => 'MedicationOrder',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedicationOrder',
      ),
      'MedicationStatement' => 
      array (
        'setter' => 'setMedicationStatement',
        'element' => 'MedicationStatement',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedicationStatement',
      ),
      'MessageHeader' => 
      array (
        'setter' => 'setMessageHeader',
        'element' => 'MessageHeader',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMessageHeader',
      ),
      'NamingSystem' => 
      array (
        'setter' => 'setNamingSystem',
        'element' => 'NamingSystem',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRNamingSystem',
      ),
      'NutritionOrder' => 
      array (
        'setter' => 'setNutritionOrder',
        'element' => 'NutritionOrder',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRNutritionOrder',
      ),
      'Observation' => 
      array (
        'setter' => 'setObservation',
        'element' => 'Observation',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRObservation',
      ),
      'OperationDefinition' => 
      array (
        'setter' => 'setOperationDefinition',
        'element' => 'OperationDefinition',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIROperationDefinition',
      ),
      'OperationOutcome' => 
      array (
        'setter' => 'setOperationOutcome',
        'element' => 'OperationOutcome',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIROperationOutcome',
      ),
      'Order' => 
      array (
        'setter' => 'setOrder',
        'element' => 'Order',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIROrder',
      ),
      'OrderResponse' => 
      array (
        'setter' => 'setOrderResponse',
        'element' => 'OrderResponse',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIROrderResponse',
      ),
      'Organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Organization',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIROrganization',
      ),
      'Patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Patient',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRPatient',
      ),
      'PaymentNotice' => 
      array (
        'setter' => 'setPaymentNotice',
        'element' => 'PaymentNotice',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRPaymentNotice',
      ),
      'PaymentReconciliation' => 
      array (
        'setter' => 'setPaymentReconciliation',
        'element' => 'PaymentReconciliation',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRPaymentReconciliation',
      ),
      'Person' => 
      array (
        'setter' => 'setPerson',
        'element' => 'Person',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRPerson',
      ),
      'Practitioner' => 
      array (
        'setter' => 'setPractitioner',
        'element' => 'Practitioner',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRPractitioner',
      ),
      'Procedure' => 
      array (
        'setter' => 'setProcedure',
        'element' => 'Procedure',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRProcedure',
      ),
      'ProcedureRequest' => 
      array (
        'setter' => 'setProcedureRequest',
        'element' => 'ProcedureRequest',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRProcedureRequest',
      ),
      'ProcessRequest' => 
      array (
        'setter' => 'setProcessRequest',
        'element' => 'ProcessRequest',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRProcessRequest',
      ),
      'ProcessResponse' => 
      array (
        'setter' => 'setProcessResponse',
        'element' => 'ProcessResponse',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRProcessResponse',
      ),
      'Provenance' => 
      array (
        'setter' => 'setProvenance',
        'element' => 'Provenance',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRProvenance',
      ),
      'Questionnaire' => 
      array (
        'setter' => 'setQuestionnaire',
        'element' => 'Questionnaire',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRQuestionnaire',
      ),
      'QuestionnaireResponse' => 
      array (
        'setter' => 'setQuestionnaireResponse',
        'element' => 'QuestionnaireResponse',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRQuestionnaireResponse',
      ),
      'ReferralRequest' => 
      array (
        'setter' => 'setReferralRequest',
        'element' => 'ReferralRequest',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRReferralRequest',
      ),
      'RelatedPerson' => 
      array (
        'setter' => 'setRelatedPerson',
        'element' => 'RelatedPerson',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRRelatedPerson',
      ),
      'RiskAssessment' => 
      array (
        'setter' => 'setRiskAssessment',
        'element' => 'RiskAssessment',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRRiskAssessment',
      ),
      'Schedule' => 
      array (
        'setter' => 'setSchedule',
        'element' => 'Schedule',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSchedule',
      ),
      'SearchParameter' => 
      array (
        'setter' => 'setSearchParameter',
        'element' => 'SearchParameter',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSearchParameter',
      ),
      'Slot' => 
      array (
        'setter' => 'setSlot',
        'element' => 'Slot',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSlot',
      ),
      'Specimen' => 
      array (
        'setter' => 'setSpecimen',
        'element' => 'Specimen',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSpecimen',
      ),
      'StructureDefinition' => 
      array (
        'setter' => 'setStructureDefinition',
        'element' => 'StructureDefinition',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRStructureDefinition',
      ),
      'Subscription' => 
      array (
        'setter' => 'setSubscription',
        'element' => 'Subscription',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSubscription',
      ),
      'Substance' => 
      array (
        'setter' => 'setSubstance',
        'element' => 'Substance',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSubstance',
      ),
      'SupplyDelivery' => 
      array (
        'setter' => 'setSupplyDelivery',
        'element' => 'SupplyDelivery',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSupplyDelivery',
      ),
      'SupplyRequest' => 
      array (
        'setter' => 'setSupplyRequest',
        'element' => 'SupplyRequest',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSupplyRequest',
      ),
      'TestScript' => 
      array (
        'setter' => 'setTestScript',
        'element' => 'TestScript',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRTestScript',
      ),
      'ValueSet' => 
      array (
        'setter' => 'setValueSet',
        'element' => 'ValueSet',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRValueSet',
      ),
      'VisionPrescription' => 
      array (
        'setter' => 'setVisionPrescription',
        'element' => 'VisionPrescription',
        'type' => 'FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRVisionPrescription',
      ),
      'Parameters' => 
      array (
        'setter' => 'setParameters',
        'element' => 'Parameters',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRParameters',
      ),
    ),
  ),
  'Extension' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'valueBoolean' => 
      array (
        'setter' => 'setValueBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'valueInteger' => 
      array (
        'setter' => 'setValueInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'valueDecimal' => 
      array (
        'setter' => 'setValueDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'valueBase64Binary' => 
      array (
        'setter' => 'setValueBase64Binary',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'valueInstant' => 
      array (
        'setter' => 'setValueInstant',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'valueString' => 
      array (
        'setter' => 'setValueString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valueUri' => 
      array (
        'setter' => 'setValueUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'valueDate' => 
      array (
        'setter' => 'setValueDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'valueDateTime' => 
      array (
        'setter' => 'setValueDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'valueTime' => 
      array (
        'setter' => 'setValueTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'valueCode' => 
      array (
        'setter' => 'setValueCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'valueOid' => 
      array (
        'setter' => 'setValueOid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'valueUuid' => 
      array (
        'setter' => 'setValueUuid',
        'element' => 'uuid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUuid',
      ),
      'valueId' => 
      array (
        'setter' => 'setValueId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'valueUnsignedInt' => 
      array (
        'setter' => 'setValueUnsignedInt',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'valuePositiveInt' => 
      array (
        'setter' => 'setValuePositiveInt',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'valueMarkdown' => 
      array (
        'setter' => 'setValueMarkdown',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'valueAnnotation' => 
      array (
        'setter' => 'setValueAnnotation',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'valueAttachment' => 
      array (
        'setter' => 'setValueAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'valueIdentifier' => 
      array (
        'setter' => 'setValueIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'valueCodeableConcept' => 
      array (
        'setter' => 'setValueCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'valueCoding' => 
      array (
        'setter' => 'setValueCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'valueQuantity' => 
      array (
        'setter' => 'setValueQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'valueRange' => 
      array (
        'setter' => 'setValueRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'valuePeriod' => 
      array (
        'setter' => 'setValuePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'valueRatio' => 
      array (
        'setter' => 'setValueRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'valueReference' => 
      array (
        'setter' => 'setValueReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'valueSampledData' => 
      array (
        'setter' => 'setValueSampledData',
        'element' => 'SampledData',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
      ),
      'valueSignature' => 
      array (
        'setter' => 'setValueSignature',
        'element' => 'Signature',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
      ),
      'valueHumanName' => 
      array (
        'setter' => 'setValueHumanName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'valueAddress' => 
      array (
        'setter' => 'setValueAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'valueContactPoint' => 
      array (
        'setter' => 'setValueContactPoint',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'valueTiming' => 
      array (
        'setter' => 'setValueTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'valueMeta' => 
      array (
        'setter' => 'setValueMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'BackboneElement' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRBackboneElement',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
    ),
  ),
  'Narrative' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'NarrativeStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrativeStatus',
      ),
      'div' => 
      array (
        'setter' => 'setDiv',
        'element' => 'html',
        'type' => 'string',
      ),
    ),
  ),
  'NarrativeStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrativeStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'NarrativeStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Element' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement',
    'extendedElementName' => NULL,
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'Period' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'start' => 
      array (
        'setter' => 'setStart',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'end' => 
      array (
        'setter' => 'setEnd',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
    ),
  ),
  'Coding' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'display' => 
      array (
        'setter' => 'setDisplay',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'userSelected' => 
      array (
        'setter' => 'setUserSelected',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
    ),
  ),
  'Range' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'low' => 
      array (
        'setter' => 'setLow',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'high' => 
      array (
        'setter' => 'setHigh',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
    ),
  ),
  'Quantity' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'comparator' => 
      array (
        'setter' => 'setComparator',
        'element' => 'QuantityComparator',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantityComparator',
      ),
      'unit' => 
      array (
        'setter' => 'setUnit',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'QuantityComparator' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantityComparator',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'QuantityComparator-list',
        'type' => 'string',
      ),
    ),
  ),
  'Attachment' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'contentType' => 
      array (
        'setter' => 'setContentType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'data' => 
      array (
        'setter' => 'setData',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'size' => 
      array (
        'setter' => 'setSize',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'hash' => 
      array (
        'setter' => 'setHash',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'title' => 
      array (
        'setter' => 'setTitle',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'creation' => 
      array (
        'setter' => 'setCreation',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
    ),
  ),
  'Ratio' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'numerator' => 
      array (
        'setter' => 'setNumerator',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'denominator' => 
      array (
        'setter' => 'setDenominator',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
    ),
  ),
  'Annotation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'authorReference' => 
      array (
        'setter' => 'setAuthorReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'authorString' => 
      array (
        'setter' => 'setAuthorString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'time' => 
      array (
        'setter' => 'setTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'SampledData' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'origin' => 
      array (
        'setter' => 'setOrigin',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'factor' => 
      array (
        'setter' => 'setFactor',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'lowerLimit' => 
      array (
        'setter' => 'setLowerLimit',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'upperLimit' => 
      array (
        'setter' => 'setUpperLimit',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'dimensions' => 
      array (
        'setter' => 'setDimensions',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'data' => 
      array (
        'setter' => 'setData',
        'element' => 'SampledDataDataType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledDataDataType',
      ),
    ),
  ),
  'SampledDataDataType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledDataDataType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SampledDataDataType-primitive',
        'type' => 'string',
      ),
    ),
  ),
  'Reference' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'reference' => 
      array (
        'setter' => 'setReference',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'display' => 
      array (
        'setter' => 'setDisplay',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'CodeableConcept' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'coding' => 
      array (
        'setter' => 'addCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Identifier' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'use' => 
      array (
        'setter' => 'setUse',
        'element' => 'IdentifierUse',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifierUse',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'assigner' => 
      array (
        'setter' => 'setAssigner',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'IdentifierUse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifierUse',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'IdentifierUse-list',
        'type' => 'string',
      ),
    ),
  ),
  'Signature' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'when' => 
      array (
        'setter' => 'setWhen',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'whoUri' => 
      array (
        'setter' => 'setWhoUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'whoReference' => 
      array (
        'setter' => 'setWhoReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'contentType' => 
      array (
        'setter' => 'setContentType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'blob' => 
      array (
        'setter' => 'setBlob',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
    ),
  ),
  'Age' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRAge',
    'extendedElementName' => 'Quantity',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'comparator' => 
      array (
        'setter' => 'setComparator',
        'element' => 'QuantityComparator',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantityComparator',
      ),
      'unit' => 
      array (
        'setter' => 'setUnit',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'Count' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRCount',
    'extendedElementName' => 'Quantity',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'comparator' => 
      array (
        'setter' => 'setComparator',
        'element' => 'QuantityComparator',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantityComparator',
      ),
      'unit' => 
      array (
        'setter' => 'setUnit',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'Money' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRMoney',
    'extendedElementName' => 'Quantity',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'comparator' => 
      array (
        'setter' => 'setComparator',
        'element' => 'QuantityComparator',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantityComparator',
      ),
      'unit' => 
      array (
        'setter' => 'setUnit',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'Distance' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDistance',
    'extendedElementName' => 'Quantity',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'comparator' => 
      array (
        'setter' => 'setComparator',
        'element' => 'QuantityComparator',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantityComparator',
      ),
      'unit' => 
      array (
        'setter' => 'setUnit',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'Duration' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDuration',
    'extendedElementName' => 'Quantity',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'comparator' => 
      array (
        'setter' => 'setComparator',
        'element' => 'QuantityComparator',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantityComparator',
      ),
      'unit' => 
      array (
        'setter' => 'setUnit',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'SimpleQuantity' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRSimpleQuantity',
    'extendedElementName' => 'Quantity',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'comparator' => 
      array (
        'setter' => 'setComparator',
        'element' => 'QuantityComparator',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantityComparator',
      ),
      'unit' => 
      array (
        'setter' => 'setUnit',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'ElementDefinition' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRElementDefinition',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'path' => 
      array (
        'setter' => 'setPath',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'representation' => 
      array (
        'setter' => 'addRepresentation',
        'element' => 'PropertyRepresentation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPropertyRepresentation',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'label' => 
      array (
        'setter' => 'setLabel',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'code' => 
      array (
        'setter' => 'addCode',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'slicing' => 
      array (
        'setter' => 'setSlicing',
        'element' => 'ElementDefinition.Slicing',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionSlicing',
      ),
      'short' => 
      array (
        'setter' => 'setShort',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'definition' => 
      array (
        'setter' => 'setDefinition',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'comments' => 
      array (
        'setter' => 'setComments',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'requirements' => 
      array (
        'setter' => 'setRequirements',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'alias' => 
      array (
        'setter' => 'addAlias',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'min' => 
      array (
        'setter' => 'setMin',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'max' => 
      array (
        'setter' => 'setMax',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'base' => 
      array (
        'setter' => 'setBase',
        'element' => 'ElementDefinition.Base',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionBase',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'ElementDefinition.Type',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionType',
      ),
      'nameReference' => 
      array (
        'setter' => 'setNameReference',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'defaultValueBoolean' => 
      array (
        'setter' => 'setDefaultValueBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'defaultValueInteger' => 
      array (
        'setter' => 'setDefaultValueInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'defaultValueDecimal' => 
      array (
        'setter' => 'setDefaultValueDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'defaultValueBase64Binary' => 
      array (
        'setter' => 'setDefaultValueBase64Binary',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'defaultValueInstant' => 
      array (
        'setter' => 'setDefaultValueInstant',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'defaultValueString' => 
      array (
        'setter' => 'setDefaultValueString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'defaultValueUri' => 
      array (
        'setter' => 'setDefaultValueUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'defaultValueDate' => 
      array (
        'setter' => 'setDefaultValueDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'defaultValueDateTime' => 
      array (
        'setter' => 'setDefaultValueDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'defaultValueTime' => 
      array (
        'setter' => 'setDefaultValueTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'defaultValueCode' => 
      array (
        'setter' => 'setDefaultValueCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'defaultValueOid' => 
      array (
        'setter' => 'setDefaultValueOid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'defaultValueUuid' => 
      array (
        'setter' => 'setDefaultValueUuid',
        'element' => 'uuid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUuid',
      ),
      'defaultValueId' => 
      array (
        'setter' => 'setDefaultValueId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'defaultValueUnsignedInt' => 
      array (
        'setter' => 'setDefaultValueUnsignedInt',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'defaultValuePositiveInt' => 
      array (
        'setter' => 'setDefaultValuePositiveInt',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'defaultValueMarkdown' => 
      array (
        'setter' => 'setDefaultValueMarkdown',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'defaultValueAnnotation' => 
      array (
        'setter' => 'setDefaultValueAnnotation',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'defaultValueAttachment' => 
      array (
        'setter' => 'setDefaultValueAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'defaultValueIdentifier' => 
      array (
        'setter' => 'setDefaultValueIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'defaultValueCodeableConcept' => 
      array (
        'setter' => 'setDefaultValueCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'defaultValueCoding' => 
      array (
        'setter' => 'setDefaultValueCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'defaultValueQuantity' => 
      array (
        'setter' => 'setDefaultValueQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'defaultValueRange' => 
      array (
        'setter' => 'setDefaultValueRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'defaultValuePeriod' => 
      array (
        'setter' => 'setDefaultValuePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'defaultValueRatio' => 
      array (
        'setter' => 'setDefaultValueRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'defaultValueReference' => 
      array (
        'setter' => 'setDefaultValueReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'defaultValueSampledData' => 
      array (
        'setter' => 'setDefaultValueSampledData',
        'element' => 'SampledData',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
      ),
      'defaultValueSignature' => 
      array (
        'setter' => 'setDefaultValueSignature',
        'element' => 'Signature',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
      ),
      'defaultValueHumanName' => 
      array (
        'setter' => 'setDefaultValueHumanName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'defaultValueAddress' => 
      array (
        'setter' => 'setDefaultValueAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'defaultValueContactPoint' => 
      array (
        'setter' => 'setDefaultValueContactPoint',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'defaultValueTiming' => 
      array (
        'setter' => 'setDefaultValueTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'defaultValueMeta' => 
      array (
        'setter' => 'setDefaultValueMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'meaningWhenMissing' => 
      array (
        'setter' => 'setMeaningWhenMissing',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'fixedBoolean' => 
      array (
        'setter' => 'setFixedBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'fixedInteger' => 
      array (
        'setter' => 'setFixedInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'fixedDecimal' => 
      array (
        'setter' => 'setFixedDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'fixedBase64Binary' => 
      array (
        'setter' => 'setFixedBase64Binary',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'fixedInstant' => 
      array (
        'setter' => 'setFixedInstant',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'fixedString' => 
      array (
        'setter' => 'setFixedString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'fixedUri' => 
      array (
        'setter' => 'setFixedUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'fixedDate' => 
      array (
        'setter' => 'setFixedDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'fixedDateTime' => 
      array (
        'setter' => 'setFixedDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'fixedTime' => 
      array (
        'setter' => 'setFixedTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'fixedCode' => 
      array (
        'setter' => 'setFixedCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'fixedOid' => 
      array (
        'setter' => 'setFixedOid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'fixedUuid' => 
      array (
        'setter' => 'setFixedUuid',
        'element' => 'uuid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUuid',
      ),
      'fixedId' => 
      array (
        'setter' => 'setFixedId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'fixedUnsignedInt' => 
      array (
        'setter' => 'setFixedUnsignedInt',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'fixedPositiveInt' => 
      array (
        'setter' => 'setFixedPositiveInt',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'fixedMarkdown' => 
      array (
        'setter' => 'setFixedMarkdown',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'fixedAnnotation' => 
      array (
        'setter' => 'setFixedAnnotation',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'fixedAttachment' => 
      array (
        'setter' => 'setFixedAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'fixedIdentifier' => 
      array (
        'setter' => 'setFixedIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'fixedCodeableConcept' => 
      array (
        'setter' => 'setFixedCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'fixedCoding' => 
      array (
        'setter' => 'setFixedCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'fixedQuantity' => 
      array (
        'setter' => 'setFixedQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'fixedRange' => 
      array (
        'setter' => 'setFixedRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'fixedPeriod' => 
      array (
        'setter' => 'setFixedPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'fixedRatio' => 
      array (
        'setter' => 'setFixedRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'fixedReference' => 
      array (
        'setter' => 'setFixedReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'fixedSampledData' => 
      array (
        'setter' => 'setFixedSampledData',
        'element' => 'SampledData',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
      ),
      'fixedSignature' => 
      array (
        'setter' => 'setFixedSignature',
        'element' => 'Signature',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
      ),
      'fixedHumanName' => 
      array (
        'setter' => 'setFixedHumanName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'fixedAddress' => 
      array (
        'setter' => 'setFixedAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'fixedContactPoint' => 
      array (
        'setter' => 'setFixedContactPoint',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'fixedTiming' => 
      array (
        'setter' => 'setFixedTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'fixedMeta' => 
      array (
        'setter' => 'setFixedMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'patternBoolean' => 
      array (
        'setter' => 'setPatternBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'patternInteger' => 
      array (
        'setter' => 'setPatternInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'patternDecimal' => 
      array (
        'setter' => 'setPatternDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'patternBase64Binary' => 
      array (
        'setter' => 'setPatternBase64Binary',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'patternInstant' => 
      array (
        'setter' => 'setPatternInstant',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'patternString' => 
      array (
        'setter' => 'setPatternString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'patternUri' => 
      array (
        'setter' => 'setPatternUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'patternDate' => 
      array (
        'setter' => 'setPatternDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'patternDateTime' => 
      array (
        'setter' => 'setPatternDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'patternTime' => 
      array (
        'setter' => 'setPatternTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'patternCode' => 
      array (
        'setter' => 'setPatternCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'patternOid' => 
      array (
        'setter' => 'setPatternOid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'patternUuid' => 
      array (
        'setter' => 'setPatternUuid',
        'element' => 'uuid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUuid',
      ),
      'patternId' => 
      array (
        'setter' => 'setPatternId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'patternUnsignedInt' => 
      array (
        'setter' => 'setPatternUnsignedInt',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'patternPositiveInt' => 
      array (
        'setter' => 'setPatternPositiveInt',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'patternMarkdown' => 
      array (
        'setter' => 'setPatternMarkdown',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'patternAnnotation' => 
      array (
        'setter' => 'setPatternAnnotation',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'patternAttachment' => 
      array (
        'setter' => 'setPatternAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'patternIdentifier' => 
      array (
        'setter' => 'setPatternIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'patternCodeableConcept' => 
      array (
        'setter' => 'setPatternCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'patternCoding' => 
      array (
        'setter' => 'setPatternCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'patternQuantity' => 
      array (
        'setter' => 'setPatternQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'patternRange' => 
      array (
        'setter' => 'setPatternRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'patternPeriod' => 
      array (
        'setter' => 'setPatternPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'patternRatio' => 
      array (
        'setter' => 'setPatternRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'patternReference' => 
      array (
        'setter' => 'setPatternReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'patternSampledData' => 
      array (
        'setter' => 'setPatternSampledData',
        'element' => 'SampledData',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
      ),
      'patternSignature' => 
      array (
        'setter' => 'setPatternSignature',
        'element' => 'Signature',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
      ),
      'patternHumanName' => 
      array (
        'setter' => 'setPatternHumanName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'patternAddress' => 
      array (
        'setter' => 'setPatternAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'patternContactPoint' => 
      array (
        'setter' => 'setPatternContactPoint',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'patternTiming' => 
      array (
        'setter' => 'setPatternTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'patternMeta' => 
      array (
        'setter' => 'setPatternMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'exampleBoolean' => 
      array (
        'setter' => 'setExampleBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'exampleInteger' => 
      array (
        'setter' => 'setExampleInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'exampleDecimal' => 
      array (
        'setter' => 'setExampleDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'exampleBase64Binary' => 
      array (
        'setter' => 'setExampleBase64Binary',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'exampleInstant' => 
      array (
        'setter' => 'setExampleInstant',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'exampleString' => 
      array (
        'setter' => 'setExampleString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'exampleUri' => 
      array (
        'setter' => 'setExampleUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'exampleDate' => 
      array (
        'setter' => 'setExampleDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'exampleDateTime' => 
      array (
        'setter' => 'setExampleDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'exampleTime' => 
      array (
        'setter' => 'setExampleTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'exampleCode' => 
      array (
        'setter' => 'setExampleCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'exampleOid' => 
      array (
        'setter' => 'setExampleOid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'exampleUuid' => 
      array (
        'setter' => 'setExampleUuid',
        'element' => 'uuid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUuid',
      ),
      'exampleId' => 
      array (
        'setter' => 'setExampleId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'exampleUnsignedInt' => 
      array (
        'setter' => 'setExampleUnsignedInt',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'examplePositiveInt' => 
      array (
        'setter' => 'setExamplePositiveInt',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'exampleMarkdown' => 
      array (
        'setter' => 'setExampleMarkdown',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'exampleAnnotation' => 
      array (
        'setter' => 'setExampleAnnotation',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'exampleAttachment' => 
      array (
        'setter' => 'setExampleAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'exampleIdentifier' => 
      array (
        'setter' => 'setExampleIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'exampleCodeableConcept' => 
      array (
        'setter' => 'setExampleCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'exampleCoding' => 
      array (
        'setter' => 'setExampleCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'exampleQuantity' => 
      array (
        'setter' => 'setExampleQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'exampleRange' => 
      array (
        'setter' => 'setExampleRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'examplePeriod' => 
      array (
        'setter' => 'setExamplePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'exampleRatio' => 
      array (
        'setter' => 'setExampleRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'exampleReference' => 
      array (
        'setter' => 'setExampleReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'exampleSampledData' => 
      array (
        'setter' => 'setExampleSampledData',
        'element' => 'SampledData',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
      ),
      'exampleSignature' => 
      array (
        'setter' => 'setExampleSignature',
        'element' => 'Signature',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
      ),
      'exampleHumanName' => 
      array (
        'setter' => 'setExampleHumanName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'exampleAddress' => 
      array (
        'setter' => 'setExampleAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'exampleContactPoint' => 
      array (
        'setter' => 'setExampleContactPoint',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'exampleTiming' => 
      array (
        'setter' => 'setExampleTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'exampleMeta' => 
      array (
        'setter' => 'setExampleMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'minValueBoolean' => 
      array (
        'setter' => 'setMinValueBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'minValueInteger' => 
      array (
        'setter' => 'setMinValueInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'minValueDecimal' => 
      array (
        'setter' => 'setMinValueDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'minValueBase64Binary' => 
      array (
        'setter' => 'setMinValueBase64Binary',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'minValueInstant' => 
      array (
        'setter' => 'setMinValueInstant',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'minValueString' => 
      array (
        'setter' => 'setMinValueString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'minValueUri' => 
      array (
        'setter' => 'setMinValueUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'minValueDate' => 
      array (
        'setter' => 'setMinValueDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'minValueDateTime' => 
      array (
        'setter' => 'setMinValueDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'minValueTime' => 
      array (
        'setter' => 'setMinValueTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'minValueCode' => 
      array (
        'setter' => 'setMinValueCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'minValueOid' => 
      array (
        'setter' => 'setMinValueOid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'minValueUuid' => 
      array (
        'setter' => 'setMinValueUuid',
        'element' => 'uuid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUuid',
      ),
      'minValueId' => 
      array (
        'setter' => 'setMinValueId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'minValueUnsignedInt' => 
      array (
        'setter' => 'setMinValueUnsignedInt',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'minValuePositiveInt' => 
      array (
        'setter' => 'setMinValuePositiveInt',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'minValueMarkdown' => 
      array (
        'setter' => 'setMinValueMarkdown',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'minValueAnnotation' => 
      array (
        'setter' => 'setMinValueAnnotation',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'minValueAttachment' => 
      array (
        'setter' => 'setMinValueAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'minValueIdentifier' => 
      array (
        'setter' => 'setMinValueIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'minValueCodeableConcept' => 
      array (
        'setter' => 'setMinValueCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'minValueCoding' => 
      array (
        'setter' => 'setMinValueCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'minValueQuantity' => 
      array (
        'setter' => 'setMinValueQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'minValueRange' => 
      array (
        'setter' => 'setMinValueRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'minValuePeriod' => 
      array (
        'setter' => 'setMinValuePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'minValueRatio' => 
      array (
        'setter' => 'setMinValueRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'minValueReference' => 
      array (
        'setter' => 'setMinValueReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'minValueSampledData' => 
      array (
        'setter' => 'setMinValueSampledData',
        'element' => 'SampledData',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
      ),
      'minValueSignature' => 
      array (
        'setter' => 'setMinValueSignature',
        'element' => 'Signature',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
      ),
      'minValueHumanName' => 
      array (
        'setter' => 'setMinValueHumanName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'minValueAddress' => 
      array (
        'setter' => 'setMinValueAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'minValueContactPoint' => 
      array (
        'setter' => 'setMinValueContactPoint',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'minValueTiming' => 
      array (
        'setter' => 'setMinValueTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'minValueMeta' => 
      array (
        'setter' => 'setMinValueMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'maxValueBoolean' => 
      array (
        'setter' => 'setMaxValueBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'maxValueInteger' => 
      array (
        'setter' => 'setMaxValueInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'maxValueDecimal' => 
      array (
        'setter' => 'setMaxValueDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'maxValueBase64Binary' => 
      array (
        'setter' => 'setMaxValueBase64Binary',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'maxValueInstant' => 
      array (
        'setter' => 'setMaxValueInstant',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'maxValueString' => 
      array (
        'setter' => 'setMaxValueString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'maxValueUri' => 
      array (
        'setter' => 'setMaxValueUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'maxValueDate' => 
      array (
        'setter' => 'setMaxValueDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'maxValueDateTime' => 
      array (
        'setter' => 'setMaxValueDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'maxValueTime' => 
      array (
        'setter' => 'setMaxValueTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'maxValueCode' => 
      array (
        'setter' => 'setMaxValueCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'maxValueOid' => 
      array (
        'setter' => 'setMaxValueOid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'maxValueUuid' => 
      array (
        'setter' => 'setMaxValueUuid',
        'element' => 'uuid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUuid',
      ),
      'maxValueId' => 
      array (
        'setter' => 'setMaxValueId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'maxValueUnsignedInt' => 
      array (
        'setter' => 'setMaxValueUnsignedInt',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'maxValuePositiveInt' => 
      array (
        'setter' => 'setMaxValuePositiveInt',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'maxValueMarkdown' => 
      array (
        'setter' => 'setMaxValueMarkdown',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'maxValueAnnotation' => 
      array (
        'setter' => 'setMaxValueAnnotation',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'maxValueAttachment' => 
      array (
        'setter' => 'setMaxValueAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'maxValueIdentifier' => 
      array (
        'setter' => 'setMaxValueIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'maxValueCodeableConcept' => 
      array (
        'setter' => 'setMaxValueCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'maxValueCoding' => 
      array (
        'setter' => 'setMaxValueCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'maxValueQuantity' => 
      array (
        'setter' => 'setMaxValueQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'maxValueRange' => 
      array (
        'setter' => 'setMaxValueRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'maxValuePeriod' => 
      array (
        'setter' => 'setMaxValuePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'maxValueRatio' => 
      array (
        'setter' => 'setMaxValueRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'maxValueReference' => 
      array (
        'setter' => 'setMaxValueReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'maxValueSampledData' => 
      array (
        'setter' => 'setMaxValueSampledData',
        'element' => 'SampledData',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
      ),
      'maxValueSignature' => 
      array (
        'setter' => 'setMaxValueSignature',
        'element' => 'Signature',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
      ),
      'maxValueHumanName' => 
      array (
        'setter' => 'setMaxValueHumanName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'maxValueAddress' => 
      array (
        'setter' => 'setMaxValueAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'maxValueContactPoint' => 
      array (
        'setter' => 'setMaxValueContactPoint',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'maxValueTiming' => 
      array (
        'setter' => 'setMaxValueTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'maxValueMeta' => 
      array (
        'setter' => 'setMaxValueMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'maxLength' => 
      array (
        'setter' => 'setMaxLength',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'condition' => 
      array (
        'setter' => 'addCondition',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'constraint' => 
      array (
        'setter' => 'addConstraint',
        'element' => 'ElementDefinition.Constraint',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionConstraint',
      ),
      'mustSupport' => 
      array (
        'setter' => 'setMustSupport',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'isModifier' => 
      array (
        'setter' => 'setIsModifier',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'isSummary' => 
      array (
        'setter' => 'setIsSummary',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'binding' => 
      array (
        'setter' => 'setBinding',
        'element' => 'ElementDefinition.Binding',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionBinding',
      ),
      'mapping' => 
      array (
        'setter' => 'addMapping',
        'element' => 'ElementDefinition.Mapping',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionMapping',
      ),
    ),
  ),
  'ElementDefinition.Mapping' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionMapping',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'identity' => 
      array (
        'setter' => 'setIdentity',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'map' => 
      array (
        'setter' => 'setMap',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'ElementDefinition.Slicing' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionSlicing',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'discriminator' => 
      array (
        'setter' => 'addDiscriminator',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'ordered' => 
      array (
        'setter' => 'setOrdered',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'rules' => 
      array (
        'setter' => 'setRules',
        'element' => 'SlicingRules',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSlicingRules',
      ),
    ),
  ),
  'ElementDefinition.Binding' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionBinding',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'strength' => 
      array (
        'setter' => 'setStrength',
        'element' => 'BindingStrength',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBindingStrength',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valueSetUri' => 
      array (
        'setter' => 'setValueSetUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'valueSetReference' => 
      array (
        'setter' => 'setValueSetReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ElementDefinition.Type' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'profile' => 
      array (
        'setter' => 'addProfile',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'aggregation' => 
      array (
        'setter' => 'addAggregation',
        'element' => 'AggregationMode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAggregationMode',
      ),
    ),
  ),
  'ElementDefinition.Base' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionBase',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'path' => 
      array (
        'setter' => 'setPath',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'min' => 
      array (
        'setter' => 'setMin',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'max' => 
      array (
        'setter' => 'setMax',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'ElementDefinition.Constraint' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRElementDefinition\\FHIRElementDefinitionConstraint',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'key' => 
      array (
        'setter' => 'setKey',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'requirements' => 
      array (
        'setter' => 'setRequirements',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'severity' => 
      array (
        'setter' => 'setSeverity',
        'element' => 'ConstraintSeverity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRConstraintSeverity',
      ),
      'human' => 
      array (
        'setter' => 'setHuman',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'xpath' => 
      array (
        'setter' => 'setXpath',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'PropertyRepresentation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRPropertyRepresentation',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'PropertyRepresentation-list',
        'type' => 'string',
      ),
    ),
  ),
  'SlicingRules' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSlicingRules',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SlicingRules-list',
        'type' => 'string',
      ),
    ),
  ),
  'BindingStrength' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRBindingStrength',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'BindingStrength-list',
        'type' => 'string',
      ),
    ),
  ),
  'AggregationMode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAggregationMode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AggregationMode-list',
        'type' => 'string',
      ),
    ),
  ),
  'ConstraintSeverity' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRConstraintSeverity',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ConstraintSeverity-list',
        'type' => 'string',
      ),
    ),
  ),
  'Timing' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'event' => 
      array (
        'setter' => 'addEvent',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'repeat' => 
      array (
        'setter' => 'setRepeat',
        'element' => 'Timing.Repeat',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTiming\\FHIRTimingRepeat',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'Timing.Repeat' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTiming\\FHIRTimingRepeat',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'boundsQuantity' => 
      array (
        'setter' => 'setBoundsQuantity',
        'element' => 'Duration',
        'type' => 'FHIR_DSTU_TWO\\FHIRDuration',
      ),
      'boundsRange' => 
      array (
        'setter' => 'setBoundsRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'boundsPeriod' => 
      array (
        'setter' => 'setBoundsPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'count' => 
      array (
        'setter' => 'setCount',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'duration' => 
      array (
        'setter' => 'setDuration',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'durationMax' => 
      array (
        'setter' => 'setDurationMax',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'durationUnits' => 
      array (
        'setter' => 'setDurationUnits',
        'element' => 'UnitsOfTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnitsOfTime',
      ),
      'frequency' => 
      array (
        'setter' => 'setFrequency',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'frequencyMax' => 
      array (
        'setter' => 'setFrequencyMax',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'periodMax' => 
      array (
        'setter' => 'setPeriodMax',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'periodUnits' => 
      array (
        'setter' => 'setPeriodUnits',
        'element' => 'UnitsOfTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnitsOfTime',
      ),
      'when' => 
      array (
        'setter' => 'setWhen',
        'element' => 'EventTiming',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIREventTiming',
      ),
    ),
  ),
  'UnitsOfTime' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRUnitsOfTime',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'UnitsOfTime-list',
        'type' => 'string',
      ),
    ),
  ),
  'EventTiming' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIREventTiming',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'EventTiming-list',
        'type' => 'string',
      ),
    ),
  ),
  'Address' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'use' => 
      array (
        'setter' => 'setUse',
        'element' => 'AddressUse',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddressUse',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'AddressType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddressType',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'line' => 
      array (
        'setter' => 'addLine',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'city' => 
      array (
        'setter' => 'setCity',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'district' => 
      array (
        'setter' => 'setDistrict',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'state' => 
      array (
        'setter' => 'setState',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'postalCode' => 
      array (
        'setter' => 'setPostalCode',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'country' => 
      array (
        'setter' => 'setCountry',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'AddressUse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAddressUse',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AddressUse-list',
        'type' => 'string',
      ),
    ),
  ),
  'AddressType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAddressType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AddressType-list',
        'type' => 'string',
      ),
    ),
  ),
  'HumanName' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'use' => 
      array (
        'setter' => 'setUse',
        'element' => 'NameUse',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNameUse',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'family' => 
      array (
        'setter' => 'addFamily',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'given' => 
      array (
        'setter' => 'addGiven',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'prefix' => 
      array (
        'setter' => 'addPrefix',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'suffix' => 
      array (
        'setter' => 'addSuffix',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'NameUse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRNameUse',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'NameUse-list',
        'type' => 'string',
      ),
    ),
  ),
  'Meta' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'versionId' => 
      array (
        'setter' => 'setVersionId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'lastUpdated' => 
      array (
        'setter' => 'setLastUpdated',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'profile' => 
      array (
        'setter' => 'addProfile',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'security' => 
      array (
        'setter' => 'addSecurity',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'tag' => 
      array (
        'setter' => 'addTag',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'ContactPoint' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'ContactPointSystem',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPointSystem',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'use' => 
      array (
        'setter' => 'setUse',
        'element' => 'ContactPointUse',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPointUse',
      ),
      'rank' => 
      array (
        'setter' => 'setRank',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'ContactPointSystem' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPointSystem',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ContactPointSystem-list',
        'type' => 'string',
      ),
    ),
  ),
  'ContactPointUse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPointUse',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ContactPointUse-list',
        'type' => 'string',
      ),
    ),
  ),
  'Resource' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource',
    'extendedElementName' => NULL,
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'DomainResource' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDomainResource',
    'extendedElementName' => 'Resource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
    ),
  ),
  'NoteType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRNoteType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'NoteType-list',
        'type' => 'string',
      ),
    ),
  ),
  'DocumentReferenceStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDocumentReferenceStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DocumentReferenceStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'ConceptMapEquivalence' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRConceptMapEquivalence',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ConceptMapEquivalence-list',
        'type' => 'string',
      ),
    ),
  ),
  'RemittanceOutcome' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRRemittanceOutcome',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'RemittanceOutcome-list',
        'type' => 'string',
      ),
    ),
  ),
  'ConformanceResourceStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRConformanceResourceStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ConformanceResourceStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'AdministrativeGender' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAdministrativeGender',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AdministrativeGender-list',
        'type' => 'string',
      ),
    ),
  ),
  'SearchParamType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSearchParamType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SearchParamType-list',
        'type' => 'string',
      ),
    ),
  ),
  'Account' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRAccount',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'AccountStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAccountStatus',
      ),
      'activePeriod' => 
      array (
        'setter' => 'setActivePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'currency' => 
      array (
        'setter' => 'setCurrency',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'balance' => 
      array (
        'setter' => 'setBalance',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'coveragePeriod' => 
      array (
        'setter' => 'setCoveragePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'owner' => 
      array (
        'setter' => 'setOwner',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'AccountStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAccountStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AccountStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'AllergyIntolerance' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRAllergyIntolerance',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'onset' => 
      array (
        'setter' => 'setOnset',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'recordedDate' => 
      array (
        'setter' => 'setRecordedDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'recorder' => 
      array (
        'setter' => 'setRecorder',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'reporter' => 
      array (
        'setter' => 'setReporter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'substance' => 
      array (
        'setter' => 'setSubstance',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'AllergyIntoleranceStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceStatus',
      ),
      'criticality' => 
      array (
        'setter' => 'setCriticality',
        'element' => 'AllergyIntoleranceCriticality',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceCriticality',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'AllergyIntoleranceType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceType',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'AllergyIntoleranceCategory',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceCategory',
      ),
      'lastOccurence' => 
      array (
        'setter' => 'setLastOccurence',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'reaction' => 
      array (
        'setter' => 'addReaction',
        'element' => 'AllergyIntolerance.Reaction',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRAllergyIntolerance\\FHIRAllergyIntoleranceReaction',
      ),
    ),
  ),
  'AllergyIntolerance.Reaction' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRAllergyIntolerance\\FHIRAllergyIntoleranceReaction',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'substance' => 
      array (
        'setter' => 'setSubstance',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'certainty' => 
      array (
        'setter' => 'setCertainty',
        'element' => 'AllergyIntoleranceCertainty',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceCertainty',
      ),
      'manifestation' => 
      array (
        'setter' => 'addManifestation',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'onset' => 
      array (
        'setter' => 'setOnset',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'severity' => 
      array (
        'setter' => 'setSeverity',
        'element' => 'AllergyIntoleranceSeverity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceSeverity',
      ),
      'exposureRoute' => 
      array (
        'setter' => 'setExposureRoute',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
    ),
  ),
  'AllergyIntoleranceCertainty' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceCertainty',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AllergyIntoleranceCertainty-list',
        'type' => 'string',
      ),
    ),
  ),
  'AllergyIntoleranceType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AllergyIntoleranceType-list',
        'type' => 'string',
      ),
    ),
  ),
  'AllergyIntoleranceSeverity' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceSeverity',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AllergyIntoleranceSeverity-list',
        'type' => 'string',
      ),
    ),
  ),
  'AllergyIntoleranceCriticality' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceCriticality',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AllergyIntoleranceCriticality-list',
        'type' => 'string',
      ),
    ),
  ),
  'AllergyIntoleranceCategory' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceCategory',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AllergyIntoleranceCategory-list',
        'type' => 'string',
      ),
    ),
  ),
  'AllergyIntoleranceStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAllergyIntoleranceStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AllergyIntoleranceStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Appointment' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRAppointment',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'AppointmentStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAppointmentStatus',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reason' => 
      array (
        'setter' => 'setReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'priority' => 
      array (
        'setter' => 'setPriority',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'start' => 
      array (
        'setter' => 'setStart',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'end' => 
      array (
        'setter' => 'setEnd',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'minutesDuration' => 
      array (
        'setter' => 'setMinutesDuration',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'slot' => 
      array (
        'setter' => 'addSlot',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'comment' => 
      array (
        'setter' => 'setComment',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'participant' => 
      array (
        'setter' => 'addParticipant',
        'element' => 'Appointment.Participant',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRAppointment\\FHIRAppointmentParticipant',
      ),
    ),
  ),
  'Appointment.Participant' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRAppointment\\FHIRAppointmentParticipant',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'actor' => 
      array (
        'setter' => 'setActor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'required' => 
      array (
        'setter' => 'setRequired',
        'element' => 'ParticipantRequired',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRParticipantRequired',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'ParticipationStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRParticipationStatus',
      ),
    ),
  ),
  'ParticipantRequired' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRParticipantRequired',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ParticipantRequired-list',
        'type' => 'string',
      ),
    ),
  ),
  'AppointmentStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAppointmentStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AppointmentStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'ParticipationStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRParticipationStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ParticipationStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'AppointmentResponse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRAppointmentResponse',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'appointment' => 
      array (
        'setter' => 'setAppointment',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'start' => 
      array (
        'setter' => 'setStart',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'end' => 
      array (
        'setter' => 'setEnd',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'participantType' => 
      array (
        'setter' => 'addParticipantType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'actor' => 
      array (
        'setter' => 'setActor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'participantStatus' => 
      array (
        'setter' => 'setParticipantStatus',
        'element' => 'ParticipantStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRParticipantStatus',
      ),
      'comment' => 
      array (
        'setter' => 'setComment',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'ParticipantStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRParticipantStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ParticipantStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'AuditEvent' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRAuditEvent',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'event' => 
      array (
        'setter' => 'setEvent',
        'element' => 'AuditEvent.Event',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventEvent',
      ),
      'participant' => 
      array (
        'setter' => 'addParticipant',
        'element' => 'AuditEvent.Participant',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventParticipant',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'AuditEvent.Source',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventSource',
      ),
      'object' => 
      array (
        'setter' => 'addObject',
        'element' => 'AuditEvent.Object',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventObject',
      ),
    ),
  ),
  'AuditEvent.Event' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventEvent',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'subtype' => 
      array (
        'setter' => 'addSubtype',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'action' => 
      array (
        'setter' => 'setAction',
        'element' => 'AuditEventAction',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAuditEventAction',
      ),
      'dateTime' => 
      array (
        'setter' => 'setDateTime',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'outcome' => 
      array (
        'setter' => 'setOutcome',
        'element' => 'AuditEventOutcome',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAuditEventOutcome',
      ),
      'outcomeDesc' => 
      array (
        'setter' => 'setOutcomeDesc',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'purposeOfEvent' => 
      array (
        'setter' => 'addPurposeOfEvent',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'AuditEvent.Participant' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventParticipant',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'role' => 
      array (
        'setter' => 'addRole',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reference' => 
      array (
        'setter' => 'setReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'userId' => 
      array (
        'setter' => 'setUserId',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'altId' => 
      array (
        'setter' => 'setAltId',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'requestor' => 
      array (
        'setter' => 'setRequestor',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'location' => 
      array (
        'setter' => 'setLocation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'policy' => 
      array (
        'setter' => 'addPolicy',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'media' => 
      array (
        'setter' => 'setMedia',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'network' => 
      array (
        'setter' => 'setNetwork',
        'element' => 'AuditEvent.Network',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventNetwork',
      ),
      'purposeOfUse' => 
      array (
        'setter' => 'addPurposeOfUse',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'AuditEvent.Network' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventNetwork',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'address' => 
      array (
        'setter' => 'setAddress',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'AuditEventParticipantNetworkType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAuditEventParticipantNetworkType',
      ),
    ),
  ),
  'AuditEvent.Source' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventSource',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'site' => 
      array (
        'setter' => 'setSite',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'AuditEvent.Object' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventObject',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'reference' => 
      array (
        'setter' => 'setReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'role' => 
      array (
        'setter' => 'setRole',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'lifecycle' => 
      array (
        'setter' => 'setLifecycle',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'securityLabel' => 
      array (
        'setter' => 'addSecurityLabel',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'query' => 
      array (
        'setter' => 'setQuery',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'detail' => 
      array (
        'setter' => 'addDetail',
        'element' => 'AuditEvent.Detail',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventDetail',
      ),
    ),
  ),
  'AuditEvent.Detail' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRAuditEvent\\FHIRAuditEventDetail',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
    ),
  ),
  'AuditEventAction' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAuditEventAction',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AuditEventAction-list',
        'type' => 'string',
      ),
    ),
  ),
  'AuditEventOutcome' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAuditEventOutcome',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AuditEventOutcome-list',
        'type' => 'string',
      ),
    ),
  ),
  'AuditEventParticipantNetworkType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAuditEventParticipantNetworkType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AuditEventParticipantNetworkType-list',
        'type' => 'string',
      ),
    ),
  ),
  'Basic' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRBasic',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
    ),
  ),
  'Binary' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRBinary',
    'extendedElementName' => 'Resource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'contentType' => 
      array (
        'setter' => 'setContentType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'content' => 
      array (
        'setter' => 'setContent',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
    ),
  ),
  'BodySite' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRBodySite',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'modifier' => 
      array (
        'setter' => 'addModifier',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'image' => 
      array (
        'setter' => 'addImage',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
    ),
  ),
  'Bundle' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle',
    'extendedElementName' => 'Resource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'BundleType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBundleType',
      ),
      'total' => 
      array (
        'setter' => 'setTotal',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'link' => 
      array (
        'setter' => 'addLink',
        'element' => 'Bundle.Link',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleLink',
      ),
      'entry' => 
      array (
        'setter' => 'addEntry',
        'element' => 'Bundle.Entry',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleEntry',
      ),
      'signature' => 
      array (
        'setter' => 'setSignature',
        'element' => 'Signature',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
      ),
    ),
  ),
  'Bundle.Link' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleLink',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'relation' => 
      array (
        'setter' => 'setRelation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
    ),
  ),
  'Bundle.Entry' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleEntry',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'link' => 
      array (
        'setter' => 'addLink',
        'element' => 'Bundle.Link',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleLink',
      ),
      'fullUrl' => 
      array (
        'setter' => 'setFullUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'resource' => 
      array (
        'setter' => 'setResource',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'search' => 
      array (
        'setter' => 'setSearch',
        'element' => 'Bundle.Search',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleSearch',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Bundle.Request',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleRequest',
      ),
      'response' => 
      array (
        'setter' => 'setResponse',
        'element' => 'Bundle.Response',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleResponse',
      ),
    ),
  ),
  'Bundle.Search' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleSearch',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'mode' => 
      array (
        'setter' => 'setMode',
        'element' => 'SearchEntryMode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSearchEntryMode',
      ),
      'score' => 
      array (
        'setter' => 'setScore',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
    ),
  ),
  'Bundle.Request' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleRequest',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'method' => 
      array (
        'setter' => 'setMethod',
        'element' => 'HTTPVerb',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHTTPVerb',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'ifNoneMatch' => 
      array (
        'setter' => 'setIfNoneMatch',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'ifModifiedSince' => 
      array (
        'setter' => 'setIfModifiedSince',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'ifMatch' => 
      array (
        'setter' => 'setIfMatch',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'ifNoneExist' => 
      array (
        'setter' => 'setIfNoneExist',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Bundle.Response' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRBundle\\FHIRBundleResponse',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'location' => 
      array (
        'setter' => 'setLocation',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'etag' => 
      array (
        'setter' => 'setEtag',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'lastModified' => 
      array (
        'setter' => 'setLastModified',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
    ),
  ),
  'HTTPVerb' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRHTTPVerb',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'HTTPVerb-list',
        'type' => 'string',
      ),
    ),
  ),
  'SearchEntryMode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSearchEntryMode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SearchEntryMode-list',
        'type' => 'string',
      ),
    ),
  ),
  'BundleType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRBundleType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'BundleType-list',
        'type' => 'string',
      ),
    ),
  ),
  'CarePlan' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRCarePlan',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'CarePlanStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCarePlanStatus',
      ),
      'context' => 
      array (
        'setter' => 'setContext',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'author' => 
      array (
        'setter' => 'addAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'modified' => 
      array (
        'setter' => 'setModified',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'category' => 
      array (
        'setter' => 'addCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'addresses' => 
      array (
        'setter' => 'addAddresses',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'support' => 
      array (
        'setter' => 'addSupport',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'relatedPlan' => 
      array (
        'setter' => 'addRelatedPlan',
        'element' => 'CarePlan.RelatedPlan',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRCarePlan\\FHIRCarePlanRelatedPlan',
      ),
      'participant' => 
      array (
        'setter' => 'addParticipant',
        'element' => 'CarePlan.Participant',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRCarePlan\\FHIRCarePlanParticipant',
      ),
      'goal' => 
      array (
        'setter' => 'addGoal',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'activity' => 
      array (
        'setter' => 'addActivity',
        'element' => 'CarePlan.Activity',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRCarePlan\\FHIRCarePlanActivity',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
    ),
  ),
  'CarePlan.RelatedPlan' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRCarePlan\\FHIRCarePlanRelatedPlan',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CarePlanRelationship',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCarePlanRelationship',
      ),
      'plan' => 
      array (
        'setter' => 'setPlan',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'CarePlan.Participant' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRCarePlan\\FHIRCarePlanParticipant',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'role' => 
      array (
        'setter' => 'setRole',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'member' => 
      array (
        'setter' => 'setMember',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'CarePlan.Activity' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRCarePlan\\FHIRCarePlanActivity',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'actionResulting' => 
      array (
        'setter' => 'addActionResulting',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'progress' => 
      array (
        'setter' => 'addProgress',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'reference' => 
      array (
        'setter' => 'setReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'detail' => 
      array (
        'setter' => 'setDetail',
        'element' => 'CarePlan.Detail',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRCarePlan\\FHIRCarePlanDetail',
      ),
    ),
  ),
  'CarePlan.Detail' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRCarePlan\\FHIRCarePlanDetail',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonCode' => 
      array (
        'setter' => 'addReasonCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonReference' => 
      array (
        'setter' => 'addReasonReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'goal' => 
      array (
        'setter' => 'addGoal',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'CarePlanActivityStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCarePlanActivityStatus',
      ),
      'statusReason' => 
      array (
        'setter' => 'setStatusReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'prohibited' => 
      array (
        'setter' => 'setProhibited',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'scheduledTiming' => 
      array (
        'setter' => 'setScheduledTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'scheduledPeriod' => 
      array (
        'setter' => 'setScheduledPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'scheduledString' => 
      array (
        'setter' => 'setScheduledString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'location' => 
      array (
        'setter' => 'setLocation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'performer' => 
      array (
        'setter' => 'addPerformer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'productCodeableConcept' => 
      array (
        'setter' => 'setProductCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'productReference' => 
      array (
        'setter' => 'setProductReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dailyAmount' => 
      array (
        'setter' => 'setDailyAmount',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'CarePlanStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRCarePlanStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'CarePlanStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'CarePlanRelationship' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRCarePlanRelationship',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'CarePlanRelationship-list',
        'type' => 'string',
      ),
    ),
  ),
  'CarePlanActivityStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRCarePlanActivityStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'CarePlanActivityStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Claim' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRClaim',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'ClaimType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRClaimType',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'provider' => 
      array (
        'setter' => 'setProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'use' => 
      array (
        'setter' => 'setUse',
        'element' => 'Use',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUse',
      ),
      'priority' => 
      array (
        'setter' => 'setPriority',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'fundsReserve' => 
      array (
        'setter' => 'setFundsReserve',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'enterer' => 
      array (
        'setter' => 'setEnterer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'facility' => 
      array (
        'setter' => 'setFacility',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'prescription' => 
      array (
        'setter' => 'setPrescription',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'originalPrescription' => 
      array (
        'setter' => 'setOriginalPrescription',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'payee' => 
      array (
        'setter' => 'setPayee',
        'element' => 'Claim.Payee',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimPayee',
      ),
      'referral' => 
      array (
        'setter' => 'setReferral',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'diagnosis' => 
      array (
        'setter' => 'addDiagnosis',
        'element' => 'Claim.Diagnosis',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimDiagnosis',
      ),
      'condition' => 
      array (
        'setter' => 'addCondition',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'coverage' => 
      array (
        'setter' => 'addCoverage',
        'element' => 'Claim.Coverage',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimCoverage',
      ),
      'exception' => 
      array (
        'setter' => 'addException',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'school' => 
      array (
        'setter' => 'setSchool',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'accident' => 
      array (
        'setter' => 'setAccident',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'accidentType' => 
      array (
        'setter' => 'setAccidentType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'interventionException' => 
      array (
        'setter' => 'addInterventionException',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'item' => 
      array (
        'setter' => 'addItem',
        'element' => 'Claim.Item',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimItem',
      ),
      'additionalMaterials' => 
      array (
        'setter' => 'addAdditionalMaterials',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'missingTeeth' => 
      array (
        'setter' => 'addMissingTeeth',
        'element' => 'Claim.MissingTeeth',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimMissingTeeth',
      ),
    ),
  ),
  'Claim.Payee' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimPayee',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'provider' => 
      array (
        'setter' => 'setProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'person' => 
      array (
        'setter' => 'setPerson',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Claim.Diagnosis' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimDiagnosis',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequence' => 
      array (
        'setter' => 'setSequence',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'diagnosis' => 
      array (
        'setter' => 'setDiagnosis',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'Claim.Coverage' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimCoverage',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequence' => 
      array (
        'setter' => 'setSequence',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'focal' => 
      array (
        'setter' => 'setFocal',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'coverage' => 
      array (
        'setter' => 'setCoverage',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'businessArrangement' => 
      array (
        'setter' => 'setBusinessArrangement',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'relationship' => 
      array (
        'setter' => 'setRelationship',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'preAuthRef' => 
      array (
        'setter' => 'addPreAuthRef',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'claimResponse' => 
      array (
        'setter' => 'setClaimResponse',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'Claim.Item' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimItem',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequence' => 
      array (
        'setter' => 'setSequence',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'provider' => 
      array (
        'setter' => 'setProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'diagnosisLinkId' => 
      array (
        'setter' => 'addDiagnosisLinkId',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'service' => 
      array (
        'setter' => 'setService',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'serviceDate' => 
      array (
        'setter' => 'setServiceDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'unitPrice' => 
      array (
        'setter' => 'setUnitPrice',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'factor' => 
      array (
        'setter' => 'setFactor',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'points' => 
      array (
        'setter' => 'setPoints',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'net' => 
      array (
        'setter' => 'setNet',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'udi' => 
      array (
        'setter' => 'setUdi',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'bodySite' => 
      array (
        'setter' => 'setBodySite',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'subSite' => 
      array (
        'setter' => 'addSubSite',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'modifier' => 
      array (
        'setter' => 'addModifier',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'detail' => 
      array (
        'setter' => 'addDetail',
        'element' => 'Claim.Detail',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimDetail',
      ),
      'prosthesis' => 
      array (
        'setter' => 'setProsthesis',
        'element' => 'Claim.Prosthesis',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimProsthesis',
      ),
    ),
  ),
  'Claim.Detail' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimDetail',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequence' => 
      array (
        'setter' => 'setSequence',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'service' => 
      array (
        'setter' => 'setService',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'unitPrice' => 
      array (
        'setter' => 'setUnitPrice',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'factor' => 
      array (
        'setter' => 'setFactor',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'points' => 
      array (
        'setter' => 'setPoints',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'net' => 
      array (
        'setter' => 'setNet',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'udi' => 
      array (
        'setter' => 'setUdi',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'subDetail' => 
      array (
        'setter' => 'addSubDetail',
        'element' => 'Claim.SubDetail',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimSubDetail',
      ),
    ),
  ),
  'Claim.SubDetail' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimSubDetail',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequence' => 
      array (
        'setter' => 'setSequence',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'service' => 
      array (
        'setter' => 'setService',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'unitPrice' => 
      array (
        'setter' => 'setUnitPrice',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'factor' => 
      array (
        'setter' => 'setFactor',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'points' => 
      array (
        'setter' => 'setPoints',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'net' => 
      array (
        'setter' => 'setNet',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'udi' => 
      array (
        'setter' => 'setUdi',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'Claim.Prosthesis' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimProsthesis',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'initial' => 
      array (
        'setter' => 'setInitial',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'priorDate' => 
      array (
        'setter' => 'setPriorDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'priorMaterial' => 
      array (
        'setter' => 'setPriorMaterial',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'Claim.MissingTeeth' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaim\\FHIRClaimMissingTeeth',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'tooth' => 
      array (
        'setter' => 'setTooth',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'reason' => 
      array (
        'setter' => 'setReason',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'extractionDate' => 
      array (
        'setter' => 'setExtractionDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
    ),
  ),
  'ClaimType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRClaimType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ClaimType-list',
        'type' => 'string',
      ),
    ),
  ),
  'Use' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRUse',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'Use-list',
        'type' => 'string',
      ),
    ),
  ),
  'ClaimResponse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRClaimResponse',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestProvider' => 
      array (
        'setter' => 'setRequestProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestOrganization' => 
      array (
        'setter' => 'setRequestOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'outcome' => 
      array (
        'setter' => 'setOutcome',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'disposition' => 
      array (
        'setter' => 'setDisposition',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'payeeType' => 
      array (
        'setter' => 'setPayeeType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'item' => 
      array (
        'setter' => 'addItem',
        'element' => 'ClaimResponse.Item',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseItem',
      ),
      'addItem' => 
      array (
        'setter' => 'addAddItem',
        'element' => 'ClaimResponse.AddItem',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAddItem',
      ),
      'error' => 
      array (
        'setter' => 'addError',
        'element' => 'ClaimResponse.Error',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseError',
      ),
      'totalCost' => 
      array (
        'setter' => 'setTotalCost',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'unallocDeductable' => 
      array (
        'setter' => 'setUnallocDeductable',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'totalBenefit' => 
      array (
        'setter' => 'setTotalBenefit',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'paymentAdjustment' => 
      array (
        'setter' => 'setPaymentAdjustment',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'paymentAdjustmentReason' => 
      array (
        'setter' => 'setPaymentAdjustmentReason',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'paymentDate' => 
      array (
        'setter' => 'setPaymentDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'paymentAmount' => 
      array (
        'setter' => 'setPaymentAmount',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'paymentRef' => 
      array (
        'setter' => 'setPaymentRef',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'reserved' => 
      array (
        'setter' => 'setReserved',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'form' => 
      array (
        'setter' => 'setForm',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'note' => 
      array (
        'setter' => 'addNote',
        'element' => 'ClaimResponse.Note',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseNote',
      ),
      'coverage' => 
      array (
        'setter' => 'addCoverage',
        'element' => 'ClaimResponse.Coverage',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseCoverage',
      ),
    ),
  ),
  'ClaimResponse.Item' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseItem',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequenceLinkId' => 
      array (
        'setter' => 'setSequenceLinkId',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'noteNumber' => 
      array (
        'setter' => 'addNoteNumber',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'adjudication' => 
      array (
        'setter' => 'addAdjudication',
        'element' => 'ClaimResponse.Adjudication',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAdjudication',
      ),
      'detail' => 
      array (
        'setter' => 'addDetail',
        'element' => 'ClaimResponse.Detail',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseDetail',
      ),
    ),
  ),
  'ClaimResponse.Adjudication' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAdjudication',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'amount' => 
      array (
        'setter' => 'setAmount',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
    ),
  ),
  'ClaimResponse.Detail' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseDetail',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequenceLinkId' => 
      array (
        'setter' => 'setSequenceLinkId',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'adjudication' => 
      array (
        'setter' => 'addAdjudication',
        'element' => 'ClaimResponse.Adjudication1',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAdjudication1',
      ),
      'subDetail' => 
      array (
        'setter' => 'addSubDetail',
        'element' => 'ClaimResponse.SubDetail',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseSubDetail',
      ),
    ),
  ),
  'ClaimResponse.Adjudication1' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAdjudication1',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'amount' => 
      array (
        'setter' => 'setAmount',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
    ),
  ),
  'ClaimResponse.SubDetail' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseSubDetail',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequenceLinkId' => 
      array (
        'setter' => 'setSequenceLinkId',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'adjudication' => 
      array (
        'setter' => 'addAdjudication',
        'element' => 'ClaimResponse.Adjudication2',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAdjudication2',
      ),
    ),
  ),
  'ClaimResponse.Adjudication2' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAdjudication2',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'amount' => 
      array (
        'setter' => 'setAmount',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
    ),
  ),
  'ClaimResponse.AddItem' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAddItem',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequenceLinkId' => 
      array (
        'setter' => 'addSequenceLinkId',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'service' => 
      array (
        'setter' => 'setService',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'fee' => 
      array (
        'setter' => 'setFee',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'noteNumberLinkId' => 
      array (
        'setter' => 'addNoteNumberLinkId',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'adjudication' => 
      array (
        'setter' => 'addAdjudication',
        'element' => 'ClaimResponse.Adjudication3',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAdjudication3',
      ),
      'detail' => 
      array (
        'setter' => 'addDetail',
        'element' => 'ClaimResponse.Detail1',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseDetail1',
      ),
    ),
  ),
  'ClaimResponse.Adjudication3' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAdjudication3',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'amount' => 
      array (
        'setter' => 'setAmount',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
    ),
  ),
  'ClaimResponse.Detail1' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseDetail1',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'service' => 
      array (
        'setter' => 'setService',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'fee' => 
      array (
        'setter' => 'setFee',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'adjudication' => 
      array (
        'setter' => 'addAdjudication',
        'element' => 'ClaimResponse.Adjudication4',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAdjudication4',
      ),
    ),
  ),
  'ClaimResponse.Adjudication4' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseAdjudication4',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'amount' => 
      array (
        'setter' => 'setAmount',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
    ),
  ),
  'ClaimResponse.Error' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseError',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequenceLinkId' => 
      array (
        'setter' => 'setSequenceLinkId',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'detailSequenceLinkId' => 
      array (
        'setter' => 'setDetailSequenceLinkId',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'subdetailSequenceLinkId' => 
      array (
        'setter' => 'setSubdetailSequenceLinkId',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'ClaimResponse.Note' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseNote',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'number' => 
      array (
        'setter' => 'setNumber',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'ClaimResponse.Coverage' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClaimResponse\\FHIRClaimResponseCoverage',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequence' => 
      array (
        'setter' => 'setSequence',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'focal' => 
      array (
        'setter' => 'setFocal',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'coverage' => 
      array (
        'setter' => 'setCoverage',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'businessArrangement' => 
      array (
        'setter' => 'setBusinessArrangement',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'relationship' => 
      array (
        'setter' => 'setRelationship',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'preAuthRef' => 
      array (
        'setter' => 'addPreAuthRef',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'claimResponse' => 
      array (
        'setter' => 'setClaimResponse',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'ClinicalImpression' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRClinicalImpression',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'assessor' => 
      array (
        'setter' => 'setAssessor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'ClinicalImpressionStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRClinicalImpressionStatus',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'previous' => 
      array (
        'setter' => 'setPrevious',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'problem' => 
      array (
        'setter' => 'addProblem',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'triggerCodeableConcept' => 
      array (
        'setter' => 'setTriggerCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'triggerReference' => 
      array (
        'setter' => 'setTriggerReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'investigations' => 
      array (
        'setter' => 'addInvestigations',
        'element' => 'ClinicalImpression.Investigations',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClinicalImpression\\FHIRClinicalImpressionInvestigations',
      ),
      'protocol' => 
      array (
        'setter' => 'setProtocol',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'summary' => 
      array (
        'setter' => 'setSummary',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'finding' => 
      array (
        'setter' => 'addFinding',
        'element' => 'ClinicalImpression.Finding',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClinicalImpression\\FHIRClinicalImpressionFinding',
      ),
      'resolved' => 
      array (
        'setter' => 'addResolved',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'ruledOut' => 
      array (
        'setter' => 'addRuledOut',
        'element' => 'ClinicalImpression.RuledOut',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRClinicalImpression\\FHIRClinicalImpressionRuledOut',
      ),
      'prognosis' => 
      array (
        'setter' => 'setPrognosis',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'plan' => 
      array (
        'setter' => 'addPlan',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'action' => 
      array (
        'setter' => 'addAction',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ClinicalImpression.Investigations' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClinicalImpression\\FHIRClinicalImpressionInvestigations',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'item' => 
      array (
        'setter' => 'addItem',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ClinicalImpression.Finding' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClinicalImpression\\FHIRClinicalImpressionFinding',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'item' => 
      array (
        'setter' => 'setItem',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'cause' => 
      array (
        'setter' => 'setCause',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'ClinicalImpression.RuledOut' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRClinicalImpression\\FHIRClinicalImpressionRuledOut',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'item' => 
      array (
        'setter' => 'setItem',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reason' => 
      array (
        'setter' => 'setReason',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'ClinicalImpressionStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRClinicalImpressionStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ClinicalImpressionStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Communication' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRCommunication',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'sender' => 
      array (
        'setter' => 'setSender',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'recipient' => 
      array (
        'setter' => 'addRecipient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'payload' => 
      array (
        'setter' => 'addPayload',
        'element' => 'Communication.Payload',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRCommunication\\FHIRCommunicationPayload',
      ),
      'medium' => 
      array (
        'setter' => 'addMedium',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'CommunicationStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCommunicationStatus',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'sent' => 
      array (
        'setter' => 'setSent',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'received' => 
      array (
        'setter' => 'setReceived',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'reason' => 
      array (
        'setter' => 'addReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestDetail' => 
      array (
        'setter' => 'setRequestDetail',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Communication.Payload' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRCommunication\\FHIRCommunicationPayload',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'contentString' => 
      array (
        'setter' => 'setContentString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contentAttachment' => 
      array (
        'setter' => 'setContentAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'contentReference' => 
      array (
        'setter' => 'setContentReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'CommunicationStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRCommunicationStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'CommunicationStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'CommunicationRequest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRCommunicationRequest',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'sender' => 
      array (
        'setter' => 'setSender',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'recipient' => 
      array (
        'setter' => 'addRecipient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'payload' => 
      array (
        'setter' => 'addPayload',
        'element' => 'CommunicationRequest.Payload',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRCommunicationRequest\\FHIRCommunicationRequestPayload',
      ),
      'medium' => 
      array (
        'setter' => 'addMedium',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'requester' => 
      array (
        'setter' => 'setRequester',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'CommunicationRequestStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCommunicationRequestStatus',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'scheduledDateTime' => 
      array (
        'setter' => 'setScheduledDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'scheduledPeriod' => 
      array (
        'setter' => 'setScheduledPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'reason' => 
      array (
        'setter' => 'addReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'requestedOn' => 
      array (
        'setter' => 'setRequestedOn',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'priority' => 
      array (
        'setter' => 'setPriority',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'CommunicationRequest.Payload' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRCommunicationRequest\\FHIRCommunicationRequestPayload',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'contentString' => 
      array (
        'setter' => 'setContentString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contentAttachment' => 
      array (
        'setter' => 'setContentAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'contentReference' => 
      array (
        'setter' => 'setContentReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'CommunicationRequestStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRCommunicationRequestStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'CommunicationRequestStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Composition' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRComposition',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'class' => 
      array (
        'setter' => 'setClass',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'title' => 
      array (
        'setter' => 'setTitle',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'CompositionStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCompositionStatus',
      ),
      'confidentiality' => 
      array (
        'setter' => 'setConfidentiality',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'author' => 
      array (
        'setter' => 'addAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'attester' => 
      array (
        'setter' => 'addAttester',
        'element' => 'Composition.Attester',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRComposition\\FHIRCompositionAttester',
      ),
      'custodian' => 
      array (
        'setter' => 'setCustodian',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'event' => 
      array (
        'setter' => 'addEvent',
        'element' => 'Composition.Event',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRComposition\\FHIRCompositionEvent',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'section' => 
      array (
        'setter' => 'addSection',
        'element' => 'Composition.Section',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRComposition\\FHIRCompositionSection',
      ),
    ),
  ),
  'Composition.Attester' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRComposition\\FHIRCompositionAttester',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'mode' => 
      array (
        'setter' => 'addMode',
        'element' => 'CompositionAttestationMode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCompositionAttestationMode',
      ),
      'time' => 
      array (
        'setter' => 'setTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'party' => 
      array (
        'setter' => 'setParty',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Composition.Event' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRComposition\\FHIRCompositionEvent',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'addCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'detail' => 
      array (
        'setter' => 'addDetail',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Composition.Section' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRComposition\\FHIRCompositionSection',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'title' => 
      array (
        'setter' => 'setTitle',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'mode' => 
      array (
        'setter' => 'setMode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'orderedBy' => 
      array (
        'setter' => 'setOrderedBy',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'entry' => 
      array (
        'setter' => 'addEntry',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'emptyReason' => 
      array (
        'setter' => 'setEmptyReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'section' => 
      array (
        'setter' => 'addSection',
        'element' => 'Composition.Section',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRComposition\\FHIRCompositionSection',
      ),
    ),
  ),
  'CompositionStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRCompositionStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'CompositionStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'CompositionAttestationMode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRCompositionAttestationMode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'CompositionAttestationMode-list',
        'type' => 'string',
      ),
    ),
  ),
  'ConceptMap' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRConceptMap',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'experimental' => 
      array (
        'setter' => 'setExperimental',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'ConceptMap.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConceptMap\\FHIRConceptMapContact',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'useContext' => 
      array (
        'setter' => 'addUseContext',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'requirements' => 
      array (
        'setter' => 'setRequirements',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'copyright' => 
      array (
        'setter' => 'setCopyright',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'sourceUri' => 
      array (
        'setter' => 'setSourceUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'sourceReference' => 
      array (
        'setter' => 'setSourceReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'targetUri' => 
      array (
        'setter' => 'setTargetUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'targetReference' => 
      array (
        'setter' => 'setTargetReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'element' => 
      array (
        'setter' => 'addElement',
        'element' => 'ConceptMap.Element',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConceptMap\\FHIRConceptMapElement',
      ),
    ),
  ),
  'ConceptMap.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConceptMap\\FHIRConceptMapContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'ConceptMap.Element' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConceptMap\\FHIRConceptMapElement',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'codeSystem' => 
      array (
        'setter' => 'setCodeSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'target' => 
      array (
        'setter' => 'addTarget',
        'element' => 'ConceptMap.Target',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConceptMap\\FHIRConceptMapTarget',
      ),
    ),
  ),
  'ConceptMap.Target' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConceptMap\\FHIRConceptMapTarget',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'codeSystem' => 
      array (
        'setter' => 'setCodeSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'equivalence' => 
      array (
        'setter' => 'setEquivalence',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'comments' => 
      array (
        'setter' => 'setComments',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'dependsOn' => 
      array (
        'setter' => 'addDependsOn',
        'element' => 'ConceptMap.DependsOn',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConceptMap\\FHIRConceptMapDependsOn',
      ),
      'product' => 
      array (
        'setter' => 'addProduct',
        'element' => 'ConceptMap.DependsOn',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConceptMap\\FHIRConceptMapDependsOn',
      ),
    ),
  ),
  'ConceptMap.DependsOn' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConceptMap\\FHIRConceptMapDependsOn',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'element' => 
      array (
        'setter' => 'setElement',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'codeSystem' => 
      array (
        'setter' => 'setCodeSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Condition' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRCondition',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'asserter' => 
      array (
        'setter' => 'setAsserter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dateRecorded' => 
      array (
        'setter' => 'setDateRecorded',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'clinicalStatus' => 
      array (
        'setter' => 'setClinicalStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'verificationStatus' => 
      array (
        'setter' => 'setVerificationStatus',
        'element' => 'ConditionVerificationStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRConditionVerificationStatus',
      ),
      'severity' => 
      array (
        'setter' => 'setSeverity',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'onsetDateTime' => 
      array (
        'setter' => 'setOnsetDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'onsetQuantity' => 
      array (
        'setter' => 'setOnsetQuantity',
        'element' => 'Age',
        'type' => 'FHIR_DSTU_TWO\\FHIRAge',
      ),
      'onsetPeriod' => 
      array (
        'setter' => 'setOnsetPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'onsetRange' => 
      array (
        'setter' => 'setOnsetRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'onsetString' => 
      array (
        'setter' => 'setOnsetString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'abatementDateTime' => 
      array (
        'setter' => 'setAbatementDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'abatementQuantity' => 
      array (
        'setter' => 'setAbatementQuantity',
        'element' => 'Age',
        'type' => 'FHIR_DSTU_TWO\\FHIRAge',
      ),
      'abatementBoolean' => 
      array (
        'setter' => 'setAbatementBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'abatementPeriod' => 
      array (
        'setter' => 'setAbatementPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'abatementRange' => 
      array (
        'setter' => 'setAbatementRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'abatementString' => 
      array (
        'setter' => 'setAbatementString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'stage' => 
      array (
        'setter' => 'setStage',
        'element' => 'Condition.Stage',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRCondition\\FHIRConditionStage',
      ),
      'evidence' => 
      array (
        'setter' => 'addEvidence',
        'element' => 'Condition.Evidence',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRCondition\\FHIRConditionEvidence',
      ),
      'bodySite' => 
      array (
        'setter' => 'addBodySite',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'notes' => 
      array (
        'setter' => 'setNotes',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Condition.Stage' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRCondition\\FHIRConditionStage',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'summary' => 
      array (
        'setter' => 'setSummary',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'assessment' => 
      array (
        'setter' => 'addAssessment',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Condition.Evidence' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRCondition\\FHIRConditionEvidence',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'detail' => 
      array (
        'setter' => 'addDetail',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ConditionVerificationStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRConditionVerificationStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ConditionVerificationStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'ConditionDefinition' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRConditionDefinition',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'experimental' => 
      array (
        'setter' => 'setExperimental',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'ConditionDefinition.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConditionDefinition\\FHIRConditionDefinitionContact',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'useContext' => 
      array (
        'setter' => 'addUseContext',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'copyright' => 
      array (
        'setter' => 'setCopyright',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'severity' => 
      array (
        'setter' => 'setSeverity',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'occurance' => 
      array (
        'setter' => 'addOccurance',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'findingSite' => 
      array (
        'setter' => 'addFindingSite',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'morphology' => 
      array (
        'setter' => 'addMorphology',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'causedBy' => 
      array (
        'setter' => 'addCausedBy',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'associated' => 
      array (
        'setter' => 'addAssociated',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ConditionDefinition.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConditionDefinition\\FHIRConditionDefinitionContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'Conformance' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRConformance',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'experimental' => 
      array (
        'setter' => 'setExperimental',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'Conformance.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceContact',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'requirements' => 
      array (
        'setter' => 'setRequirements',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'copyright' => 
      array (
        'setter' => 'setCopyright',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'kind' => 
      array (
        'setter' => 'setKind',
        'element' => 'ConformanceStatementKind',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRConformanceStatementKind',
      ),
      'software' => 
      array (
        'setter' => 'setSoftware',
        'element' => 'Conformance.Software',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceSoftware',
      ),
      'implementation' => 
      array (
        'setter' => 'setImplementation',
        'element' => 'Conformance.Implementation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceImplementation',
      ),
      'fhirVersion' => 
      array (
        'setter' => 'setFhirVersion',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'acceptUnknown' => 
      array (
        'setter' => 'setAcceptUnknown',
        'element' => 'UnknownContentCode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnknownContentCode',
      ),
      'format' => 
      array (
        'setter' => 'addFormat',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'profile' => 
      array (
        'setter' => 'addProfile',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'rest' => 
      array (
        'setter' => 'addRest',
        'element' => 'Conformance.Rest',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceRest',
      ),
      'messaging' => 
      array (
        'setter' => 'addMessaging',
        'element' => 'Conformance.Messaging',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceMessaging',
      ),
      'document' => 
      array (
        'setter' => 'addDocument',
        'element' => 'Conformance.Document',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceDocument',
      ),
    ),
  ),
  'Conformance.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'Conformance.Software' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceSoftware',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'releaseDate' => 
      array (
        'setter' => 'setReleaseDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
    ),
  ),
  'Conformance.Implementation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceImplementation',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
    ),
  ),
  'Conformance.Rest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceRest',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'mode' => 
      array (
        'setter' => 'setMode',
        'element' => 'RestfulConformanceMode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRestfulConformanceMode',
      ),
      'documentation' => 
      array (
        'setter' => 'setDocumentation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'security' => 
      array (
        'setter' => 'setSecurity',
        'element' => 'Conformance.Security',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceSecurity',
      ),
      'resource' => 
      array (
        'setter' => 'addResource',
        'element' => 'Conformance.Resource',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceResource',
      ),
      'interaction' => 
      array (
        'setter' => 'addInteraction',
        'element' => 'Conformance.Interaction1',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceInteraction1',
      ),
      'transactionMode' => 
      array (
        'setter' => 'setTransactionMode',
        'element' => 'TransactionMode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTransactionMode',
      ),
      'searchParam' => 
      array (
        'setter' => 'addSearchParam',
        'element' => 'Conformance.SearchParam',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceSearchParam',
      ),
      'operation' => 
      array (
        'setter' => 'addOperation',
        'element' => 'Conformance.Operation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceOperation',
      ),
      'compartment' => 
      array (
        'setter' => 'addCompartment',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
    ),
  ),
  'Conformance.Security' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceSecurity',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'cors' => 
      array (
        'setter' => 'setCors',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'service' => 
      array (
        'setter' => 'addService',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'certificate' => 
      array (
        'setter' => 'addCertificate',
        'element' => 'Conformance.Certificate',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceCertificate',
      ),
    ),
  ),
  'Conformance.Certificate' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceCertificate',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'blob' => 
      array (
        'setter' => 'setBlob',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
    ),
  ),
  'Conformance.Resource' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceResource',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'profile' => 
      array (
        'setter' => 'setProfile',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'interaction' => 
      array (
        'setter' => 'addInteraction',
        'element' => 'Conformance.Interaction',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceInteraction',
      ),
      'versioning' => 
      array (
        'setter' => 'setVersioning',
        'element' => 'ResourceVersionPolicy',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRResourceVersionPolicy',
      ),
      'readHistory' => 
      array (
        'setter' => 'setReadHistory',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'updateCreate' => 
      array (
        'setter' => 'setUpdateCreate',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'conditionalCreate' => 
      array (
        'setter' => 'setConditionalCreate',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'conditionalUpdate' => 
      array (
        'setter' => 'setConditionalUpdate',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'conditionalDelete' => 
      array (
        'setter' => 'setConditionalDelete',
        'element' => 'ConditionalDeleteStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRConditionalDeleteStatus',
      ),
      'searchInclude' => 
      array (
        'setter' => 'addSearchInclude',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'searchRevInclude' => 
      array (
        'setter' => 'addSearchRevInclude',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'searchParam' => 
      array (
        'setter' => 'addSearchParam',
        'element' => 'Conformance.SearchParam',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceSearchParam',
      ),
    ),
  ),
  'Conformance.Interaction' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceInteraction',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'TypeRestfulInteraction',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTypeRestfulInteraction',
      ),
      'documentation' => 
      array (
        'setter' => 'setDocumentation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Conformance.SearchParam' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceSearchParam',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'definition' => 
      array (
        'setter' => 'setDefinition',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'documentation' => 
      array (
        'setter' => 'setDocumentation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'target' => 
      array (
        'setter' => 'addTarget',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'modifier' => 
      array (
        'setter' => 'addModifier',
        'element' => 'SearchModifierCode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSearchModifierCode',
      ),
      'chain' => 
      array (
        'setter' => 'addChain',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Conformance.Interaction1' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceInteraction1',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'SystemRestfulInteraction',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSystemRestfulInteraction',
      ),
      'documentation' => 
      array (
        'setter' => 'setDocumentation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Conformance.Operation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceOperation',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'definition' => 
      array (
        'setter' => 'setDefinition',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Conformance.Messaging' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceMessaging',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'endpoint' => 
      array (
        'setter' => 'addEndpoint',
        'element' => 'Conformance.Endpoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceEndpoint',
      ),
      'reliableCache' => 
      array (
        'setter' => 'setReliableCache',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'documentation' => 
      array (
        'setter' => 'setDocumentation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'event' => 
      array (
        'setter' => 'addEvent',
        'element' => 'Conformance.Event',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceEvent',
      ),
    ),
  ),
  'Conformance.Endpoint' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceEndpoint',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'protocol' => 
      array (
        'setter' => 'setProtocol',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'address' => 
      array (
        'setter' => 'setAddress',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
    ),
  ),
  'Conformance.Event' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceEvent',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'MessageSignificanceCategory',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMessageSignificanceCategory',
      ),
      'mode' => 
      array (
        'setter' => 'setMode',
        'element' => 'ConformanceEventMode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRConformanceEventMode',
      ),
      'focus' => 
      array (
        'setter' => 'setFocus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'response' => 
      array (
        'setter' => 'setResponse',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'documentation' => 
      array (
        'setter' => 'setDocumentation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Conformance.Document' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRConformance\\FHIRConformanceDocument',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'mode' => 
      array (
        'setter' => 'setMode',
        'element' => 'DocumentMode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDocumentMode',
      ),
      'documentation' => 
      array (
        'setter' => 'setDocumentation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'profile' => 
      array (
        'setter' => 'setProfile',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'DocumentMode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDocumentMode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DocumentMode-list',
        'type' => 'string',
      ),
    ),
  ),
  'SystemRestfulInteraction' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSystemRestfulInteraction',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SystemRestfulInteraction-list',
        'type' => 'string',
      ),
    ),
  ),
  'ResourceVersionPolicy' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRResourceVersionPolicy',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ResourceVersionPolicy-list',
        'type' => 'string',
      ),
    ),
  ),
  'RestfulConformanceMode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRRestfulConformanceMode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'RestfulConformanceMode-list',
        'type' => 'string',
      ),
    ),
  ),
  'TypeRestfulInteraction' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRTypeRestfulInteraction',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'TypeRestfulInteraction-list',
        'type' => 'string',
      ),
    ),
  ),
  'ConditionalDeleteStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRConditionalDeleteStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ConditionalDeleteStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'ConformanceStatementKind' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRConformanceStatementKind',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ConformanceStatementKind-list',
        'type' => 'string',
      ),
    ),
  ),
  'SearchModifierCode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSearchModifierCode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SearchModifierCode-list',
        'type' => 'string',
      ),
    ),
  ),
  'ConformanceEventMode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRConformanceEventMode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ConformanceEventMode-list',
        'type' => 'string',
      ),
    ),
  ),
  'MessageSignificanceCategory' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRMessageSignificanceCategory',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'MessageSignificanceCategory-list',
        'type' => 'string',
      ),
    ),
  ),
  'UnknownContentCode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRUnknownContentCode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'UnknownContentCode-list',
        'type' => 'string',
      ),
    ),
  ),
  'TransactionMode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRTransactionMode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'TransactionMode-list',
        'type' => 'string',
      ),
    ),
  ),
  'Contract' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRContract',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'issued' => 
      array (
        'setter' => 'setIssued',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'applies' => 
      array (
        'setter' => 'setApplies',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'subject' => 
      array (
        'setter' => 'addSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'authority' => 
      array (
        'setter' => 'addAuthority',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'domain' => 
      array (
        'setter' => 'addDomain',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'subType' => 
      array (
        'setter' => 'addSubType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'action' => 
      array (
        'setter' => 'addAction',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'actionReason' => 
      array (
        'setter' => 'addActionReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'actor' => 
      array (
        'setter' => 'addActor',
        'element' => 'Contract.Actor',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractActor',
      ),
      'valuedItem' => 
      array (
        'setter' => 'addValuedItem',
        'element' => 'Contract.ValuedItem',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractValuedItem',
      ),
      'signer' => 
      array (
        'setter' => 'addSigner',
        'element' => 'Contract.Signer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractSigner',
      ),
      'term' => 
      array (
        'setter' => 'addTerm',
        'element' => 'Contract.Term',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractTerm',
      ),
      'bindingAttachment' => 
      array (
        'setter' => 'setBindingAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'bindingReference' => 
      array (
        'setter' => 'setBindingReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'friendly' => 
      array (
        'setter' => 'addFriendly',
        'element' => 'Contract.Friendly',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractFriendly',
      ),
      'legal' => 
      array (
        'setter' => 'addLegal',
        'element' => 'Contract.Legal',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractLegal',
      ),
      'rule' => 
      array (
        'setter' => 'addRule',
        'element' => 'Contract.Rule',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractRule',
      ),
    ),
  ),
  'Contract.Actor' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractActor',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'entity' => 
      array (
        'setter' => 'setEntity',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'role' => 
      array (
        'setter' => 'addRole',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'Contract.ValuedItem' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractValuedItem',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'entityCodeableConcept' => 
      array (
        'setter' => 'setEntityCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'entityReference' => 
      array (
        'setter' => 'setEntityReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'effectiveTime' => 
      array (
        'setter' => 'setEffectiveTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'unitPrice' => 
      array (
        'setter' => 'setUnitPrice',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'factor' => 
      array (
        'setter' => 'setFactor',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'points' => 
      array (
        'setter' => 'setPoints',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'net' => 
      array (
        'setter' => 'setNet',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
    ),
  ),
  'Contract.Signer' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractSigner',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'party' => 
      array (
        'setter' => 'setParty',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'signature' => 
      array (
        'setter' => 'setSignature',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Contract.Term' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractTerm',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'issued' => 
      array (
        'setter' => 'setIssued',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'applies' => 
      array (
        'setter' => 'setApplies',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'subType' => 
      array (
        'setter' => 'setSubType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'action' => 
      array (
        'setter' => 'addAction',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'actionReason' => 
      array (
        'setter' => 'addActionReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'actor' => 
      array (
        'setter' => 'addActor',
        'element' => 'Contract.Actor1',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractActor1',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valuedItem' => 
      array (
        'setter' => 'addValuedItem',
        'element' => 'Contract.ValuedItem1',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractValuedItem1',
      ),
      'group' => 
      array (
        'setter' => 'addGroup',
        'element' => 'Contract.Term',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractTerm',
      ),
    ),
  ),
  'Contract.Actor1' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractActor1',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'entity' => 
      array (
        'setter' => 'setEntity',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'role' => 
      array (
        'setter' => 'addRole',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'Contract.ValuedItem1' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractValuedItem1',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'entityCodeableConcept' => 
      array (
        'setter' => 'setEntityCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'entityReference' => 
      array (
        'setter' => 'setEntityReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'effectiveTime' => 
      array (
        'setter' => 'setEffectiveTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'unitPrice' => 
      array (
        'setter' => 'setUnitPrice',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'factor' => 
      array (
        'setter' => 'setFactor',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'points' => 
      array (
        'setter' => 'setPoints',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'net' => 
      array (
        'setter' => 'setNet',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
    ),
  ),
  'Contract.Friendly' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractFriendly',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'contentAttachment' => 
      array (
        'setter' => 'setContentAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'contentReference' => 
      array (
        'setter' => 'setContentReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Contract.Legal' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractLegal',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'contentAttachment' => 
      array (
        'setter' => 'setContentAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'contentReference' => 
      array (
        'setter' => 'setContentReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Contract.Rule' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRContract\\FHIRContractRule',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'contentAttachment' => 
      array (
        'setter' => 'setContentAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'contentReference' => 
      array (
        'setter' => 'setContentReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Contraindication' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRContraindication',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'severity' => 
      array (
        'setter' => 'setSeverity',
        'element' => 'ContraindicationSeverity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContraindicationSeverity',
      ),
      'implicated' => 
      array (
        'setter' => 'addImplicated',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'detail' => 
      array (
        'setter' => 'setDetail',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'reference' => 
      array (
        'setter' => 'setReference',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'mitigation' => 
      array (
        'setter' => 'addMitigation',
        'element' => 'Contraindication.Mitigation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRContraindication\\FHIRContraindicationMitigation',
      ),
    ),
  ),
  'Contraindication.Mitigation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRContraindication\\FHIRContraindicationMitigation',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'action' => 
      array (
        'setter' => 'setAction',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ContraindicationSeverity' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRContraindicationSeverity',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ContraindicationSeverity-list',
        'type' => 'string',
      ),
    ),
  ),
  'Coverage' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRCoverage',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'issuer' => 
      array (
        'setter' => 'setIssuer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'bin' => 
      array (
        'setter' => 'setBin',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'subscriberId' => 
      array (
        'setter' => 'setSubscriberId',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'group' => 
      array (
        'setter' => 'setGroup',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'plan' => 
      array (
        'setter' => 'setPlan',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'subPlan' => 
      array (
        'setter' => 'setSubPlan',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'dependent' => 
      array (
        'setter' => 'setDependent',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'sequence' => 
      array (
        'setter' => 'setSequence',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'subscriber' => 
      array (
        'setter' => 'setSubscriber',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'network' => 
      array (
        'setter' => 'setNetwork',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'contract' => 
      array (
        'setter' => 'addContract',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'DataElement' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDataElement',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'experimental' => 
      array (
        'setter' => 'setExperimental',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'DataElement.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDataElement\\FHIRDataElementContact',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'useContext' => 
      array (
        'setter' => 'addUseContext',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'copyright' => 
      array (
        'setter' => 'setCopyright',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'stringency' => 
      array (
        'setter' => 'setStringency',
        'element' => 'DataElementStringency',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDataElementStringency',
      ),
      'mapping' => 
      array (
        'setter' => 'addMapping',
        'element' => 'DataElement.Mapping',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDataElement\\FHIRDataElementMapping',
      ),
      'element' => 
      array (
        'setter' => 'addElement',
        'element' => 'ElementDefinition',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRElementDefinition',
      ),
    ),
  ),
  'DataElement.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDataElement\\FHIRDataElementContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'DataElement.Mapping' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDataElement\\FHIRDataElementMapping',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identity' => 
      array (
        'setter' => 'setIdentity',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'uri' => 
      array (
        'setter' => 'setUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'comments' => 
      array (
        'setter' => 'setComments',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'DataElementStringency' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDataElementStringency',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DataElementStringency-list',
        'type' => 'string',
      ),
    ),
  ),
  'DetectedIssue' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDetectedIssue',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'severity' => 
      array (
        'setter' => 'setSeverity',
        'element' => 'DetectedIssueSeverity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDetectedIssueSeverity',
      ),
      'implicated' => 
      array (
        'setter' => 'addImplicated',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'detail' => 
      array (
        'setter' => 'setDetail',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'reference' => 
      array (
        'setter' => 'setReference',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'mitigation' => 
      array (
        'setter' => 'addMitigation',
        'element' => 'DetectedIssue.Mitigation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDetectedIssue\\FHIRDetectedIssueMitigation',
      ),
    ),
  ),
  'DetectedIssue.Mitigation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDetectedIssue\\FHIRDetectedIssueMitigation',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'action' => 
      array (
        'setter' => 'setAction',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'DetectedIssueSeverity' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDetectedIssueSeverity',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DetectedIssueSeverity-list',
        'type' => 'string',
      ),
    ),
  ),
  'Device' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDevice',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'note' => 
      array (
        'setter' => 'addNote',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'DeviceStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceStatus',
      ),
      'manufacturer' => 
      array (
        'setter' => 'setManufacturer',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'model' => 
      array (
        'setter' => 'setModel',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'manufactureDate' => 
      array (
        'setter' => 'setManufactureDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'expiry' => 
      array (
        'setter' => 'setExpiry',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'udi' => 
      array (
        'setter' => 'setUdi',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'lotNumber' => 
      array (
        'setter' => 'setLotNumber',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'owner' => 
      array (
        'setter' => 'setOwner',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'location' => 
      array (
        'setter' => 'setLocation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
    ),
  ),
  'DeviceStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DeviceStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'DeviceComponent' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDeviceComponent',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'lastSystemChange' => 
      array (
        'setter' => 'setLastSystemChange',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'parent' => 
      array (
        'setter' => 'setParent',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'operationalStatus' => 
      array (
        'setter' => 'addOperationalStatus',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'parameterGroup' => 
      array (
        'setter' => 'setParameterGroup',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'measurementPrinciple' => 
      array (
        'setter' => 'setMeasurementPrinciple',
        'element' => 'Measmnt-Principle',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeasmnt_Principle',
      ),
      'productionSpecification' => 
      array (
        'setter' => 'addProductionSpecification',
        'element' => 'DeviceComponent.ProductionSpecification',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDeviceComponent\\FHIRDeviceComponentProductionSpecification',
      ),
      'languageCode' => 
      array (
        'setter' => 'setLanguageCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'DeviceComponent.ProductionSpecification' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDeviceComponent\\FHIRDeviceComponentProductionSpecification',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'specType' => 
      array (
        'setter' => 'setSpecType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'componentId' => 
      array (
        'setter' => 'setComponentId',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'productionSpec' => 
      array (
        'setter' => 'setProductionSpec',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Measmnt-Principle' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRMeasmnt_Principle',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'Measmnt-Principle-list',
        'type' => 'string',
      ),
    ),
  ),
  'DeviceMetric' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDeviceMetric',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'unit' => 
      array (
        'setter' => 'setUnit',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'parent' => 
      array (
        'setter' => 'setParent',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'operationalStatus' => 
      array (
        'setter' => 'setOperationalStatus',
        'element' => 'DeviceMetricOperationalStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceMetricOperationalStatus',
      ),
      'color' => 
      array (
        'setter' => 'setColor',
        'element' => 'DeviceMetricColor',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceMetricColor',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'DeviceMetricCategory',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceMetricCategory',
      ),
      'measurementPeriod' => 
      array (
        'setter' => 'setMeasurementPeriod',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'calibration' => 
      array (
        'setter' => 'addCalibration',
        'element' => 'DeviceMetric.Calibration',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDeviceMetric\\FHIRDeviceMetricCalibration',
      ),
    ),
  ),
  'DeviceMetric.Calibration' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDeviceMetric\\FHIRDeviceMetricCalibration',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'DeviceMetricCalibrationType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceMetricCalibrationType',
      ),
      'state' => 
      array (
        'setter' => 'setState',
        'element' => 'DeviceMetricCalibrationState',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceMetricCalibrationState',
      ),
      'time' => 
      array (
        'setter' => 'setTime',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
    ),
  ),
  'DeviceMetricColor' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceMetricColor',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DeviceMetricColor-list',
        'type' => 'string',
      ),
    ),
  ),
  'DeviceMetricCalibrationState' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceMetricCalibrationState',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DeviceMetricCalibrationState-list',
        'type' => 'string',
      ),
    ),
  ),
  'DeviceMetricCalibrationType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceMetricCalibrationType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DeviceMetricCalibrationType-list',
        'type' => 'string',
      ),
    ),
  ),
  'DeviceMetricCategory' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceMetricCategory',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DeviceMetricCategory-list',
        'type' => 'string',
      ),
    ),
  ),
  'DeviceMetricOperationalStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceMetricOperationalStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DeviceMetricOperationalStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'DeviceUseRequest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDeviceUseRequest',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'bodySiteCodeableConcept' => 
      array (
        'setter' => 'setBodySiteCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'bodySiteReference' => 
      array (
        'setter' => 'setBodySiteReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'DeviceUseRequestStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceUseRequestStatus',
      ),
      'device' => 
      array (
        'setter' => 'setDevice',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'indication' => 
      array (
        'setter' => 'addIndication',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'notes' => 
      array (
        'setter' => 'addNotes',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'prnReason' => 
      array (
        'setter' => 'addPrnReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'orderedOn' => 
      array (
        'setter' => 'setOrderedOn',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'recordedOn' => 
      array (
        'setter' => 'setRecordedOn',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'timingTiming' => 
      array (
        'setter' => 'setTimingTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'timingPeriod' => 
      array (
        'setter' => 'setTimingPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'timingDateTime' => 
      array (
        'setter' => 'setTimingDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'priority' => 
      array (
        'setter' => 'setPriority',
        'element' => 'DeviceUseRequestPriority',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceUseRequestPriority',
      ),
    ),
  ),
  'DeviceUseRequestStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceUseRequestStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DeviceUseRequestStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'DeviceUseRequestPriority' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDeviceUseRequestPriority',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DeviceUseRequestPriority-list',
        'type' => 'string',
      ),
    ),
  ),
  'DeviceUseStatement' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDeviceUseStatement',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'bodySiteCodeableConcept' => 
      array (
        'setter' => 'setBodySiteCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'bodySiteReference' => 
      array (
        'setter' => 'setBodySiteReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'whenUsed' => 
      array (
        'setter' => 'setWhenUsed',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'device' => 
      array (
        'setter' => 'setDevice',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'indication' => 
      array (
        'setter' => 'addIndication',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'notes' => 
      array (
        'setter' => 'addNotes',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'recordedOn' => 
      array (
        'setter' => 'setRecordedOn',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'timingTiming' => 
      array (
        'setter' => 'setTimingTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'timingPeriod' => 
      array (
        'setter' => 'setTimingPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'timingDateTime' => 
      array (
        'setter' => 'setTimingDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
    ),
  ),
  'DiagnosticOrder' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDiagnosticOrder',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'orderer' => 
      array (
        'setter' => 'setOrderer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'reason' => 
      array (
        'setter' => 'addReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'supportingInformation' => 
      array (
        'setter' => 'addSupportingInformation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'specimen' => 
      array (
        'setter' => 'addSpecimen',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'DiagnosticOrderStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDiagnosticOrderStatus',
      ),
      'priority' => 
      array (
        'setter' => 'setPriority',
        'element' => 'DiagnosticOrderPriority',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDiagnosticOrderPriority',
      ),
      'event' => 
      array (
        'setter' => 'addEvent',
        'element' => 'DiagnosticOrder.Event',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDiagnosticOrder\\FHIRDiagnosticOrderEvent',
      ),
      'item' => 
      array (
        'setter' => 'addItem',
        'element' => 'DiagnosticOrder.Item',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDiagnosticOrder\\FHIRDiagnosticOrderItem',
      ),
      'note' => 
      array (
        'setter' => 'addNote',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
    ),
  ),
  'DiagnosticOrder.Event' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDiagnosticOrder\\FHIRDiagnosticOrderEvent',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'DiagnosticOrderStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDiagnosticOrderStatus',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'dateTime' => 
      array (
        'setter' => 'setDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'actor' => 
      array (
        'setter' => 'setActor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'DiagnosticOrder.Item' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDiagnosticOrder\\FHIRDiagnosticOrderItem',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'specimen' => 
      array (
        'setter' => 'addSpecimen',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'bodySite' => 
      array (
        'setter' => 'setBodySite',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'DiagnosticOrderStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDiagnosticOrderStatus',
      ),
      'event' => 
      array (
        'setter' => 'addEvent',
        'element' => 'DiagnosticOrder.Event',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDiagnosticOrder\\FHIRDiagnosticOrderEvent',
      ),
    ),
  ),
  'DiagnosticOrderStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDiagnosticOrderStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DiagnosticOrderStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'DiagnosticOrderPriority' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDiagnosticOrderPriority',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DiagnosticOrderPriority-list',
        'type' => 'string',
      ),
    ),
  ),
  'DiagnosticReport' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDiagnosticReport',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'DiagnosticReportStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDiagnosticReportStatus',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'effectiveDateTime' => 
      array (
        'setter' => 'setEffectiveDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'effectivePeriod' => 
      array (
        'setter' => 'setEffectivePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'issued' => 
      array (
        'setter' => 'setIssued',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'performer' => 
      array (
        'setter' => 'setPerformer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'request' => 
      array (
        'setter' => 'addRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'specimen' => 
      array (
        'setter' => 'addSpecimen',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'result' => 
      array (
        'setter' => 'addResult',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'imagingStudy' => 
      array (
        'setter' => 'addImagingStudy',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'image' => 
      array (
        'setter' => 'addImage',
        'element' => 'DiagnosticReport.Image',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDiagnosticReport\\FHIRDiagnosticReportImage',
      ),
      'conclusion' => 
      array (
        'setter' => 'setConclusion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'codedDiagnosis' => 
      array (
        'setter' => 'addCodedDiagnosis',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'presentedForm' => 
      array (
        'setter' => 'addPresentedForm',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
    ),
  ),
  'DiagnosticReport.Image' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDiagnosticReport\\FHIRDiagnosticReportImage',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'comment' => 
      array (
        'setter' => 'setComment',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'link' => 
      array (
        'setter' => 'setLink',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'DiagnosticReportStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDiagnosticReportStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DiagnosticReportStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'DocumentManifest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDocumentManifest',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'masterIdentifier' => 
      array (
        'setter' => 'setMasterIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'recipient' => 
      array (
        'setter' => 'addRecipient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'author' => 
      array (
        'setter' => 'addAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'content' => 
      array (
        'setter' => 'addContent',
        'element' => 'DocumentManifest.Content',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentManifest\\FHIRDocumentManifestContent',
      ),
      'related' => 
      array (
        'setter' => 'addRelated',
        'element' => 'DocumentManifest.Related',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentManifest\\FHIRDocumentManifestRelated',
      ),
    ),
  ),
  'DocumentManifest.Content' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentManifest\\FHIRDocumentManifestContent',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'pAttachment' => 
      array (
        'setter' => 'setPAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'pReference' => 
      array (
        'setter' => 'setPReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'DocumentManifest.Related' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentManifest\\FHIRDocumentManifestRelated',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'ref' => 
      array (
        'setter' => 'setRef',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'DocumentReference' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRDocumentReference',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'masterIdentifier' => 
      array (
        'setter' => 'setMasterIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'class' => 
      array (
        'setter' => 'setClass',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'author' => 
      array (
        'setter' => 'addAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'custodian' => 
      array (
        'setter' => 'setCustodian',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'authenticator' => 
      array (
        'setter' => 'setAuthenticator',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'indexed' => 
      array (
        'setter' => 'setIndexed',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'docStatus' => 
      array (
        'setter' => 'setDocStatus',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'relatesTo' => 
      array (
        'setter' => 'addRelatesTo',
        'element' => 'DocumentReference.RelatesTo',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentReference\\FHIRDocumentReferenceRelatesTo',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'securityLabel' => 
      array (
        'setter' => 'addSecurityLabel',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'content' => 
      array (
        'setter' => 'addContent',
        'element' => 'DocumentReference.Content',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentReference\\FHIRDocumentReferenceContent',
      ),
      'context' => 
      array (
        'setter' => 'setContext',
        'element' => 'DocumentReference.Context',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentReference\\FHIRDocumentReferenceContext',
      ),
    ),
  ),
  'DocumentReference.RelatesTo' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentReference\\FHIRDocumentReferenceRelatesTo',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'DocumentRelationshipType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDocumentRelationshipType',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'DocumentReference.Content' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentReference\\FHIRDocumentReferenceContent',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'attachment' => 
      array (
        'setter' => 'setAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'format' => 
      array (
        'setter' => 'addFormat',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'DocumentReference.Context' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentReference\\FHIRDocumentReferenceContext',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'event' => 
      array (
        'setter' => 'addEvent',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'facilityType' => 
      array (
        'setter' => 'setFacilityType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'practiceSetting' => 
      array (
        'setter' => 'setPracticeSetting',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'sourcePatientInfo' => 
      array (
        'setter' => 'setSourcePatientInfo',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'related' => 
      array (
        'setter' => 'addRelated',
        'element' => 'DocumentReference.Related',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentReference\\FHIRDocumentReferenceRelated',
      ),
    ),
  ),
  'DocumentReference.Related' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRDocumentReference\\FHIRDocumentReferenceRelated',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'ref' => 
      array (
        'setter' => 'setRef',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'DocumentRelationshipType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDocumentRelationshipType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DocumentRelationshipType-list',
        'type' => 'string',
      ),
    ),
  ),
  'EligibilityRequest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREligibilityRequest',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'provider' => 
      array (
        'setter' => 'setProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'EligibilityResponse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREligibilityResponse',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'outcome' => 
      array (
        'setter' => 'setOutcome',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'disposition' => 
      array (
        'setter' => 'setDisposition',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestProvider' => 
      array (
        'setter' => 'setRequestProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestOrganization' => 
      array (
        'setter' => 'setRequestOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Encounter' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREncounter',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'EncounterState',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIREncounterState',
      ),
      'statusHistory' => 
      array (
        'setter' => 'addStatusHistory',
        'element' => 'Encounter.StatusHistory',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIREncounter\\FHIREncounterStatusHistory',
      ),
      'class' => 
      array (
        'setter' => 'setClass',
        'element' => 'EncounterClass',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIREncounterClass',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'priority' => 
      array (
        'setter' => 'setPriority',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'episodeOfCare' => 
      array (
        'setter' => 'addEpisodeOfCare',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'incomingReferral' => 
      array (
        'setter' => 'addIncomingReferral',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'participant' => 
      array (
        'setter' => 'addParticipant',
        'element' => 'Encounter.Participant',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIREncounter\\FHIREncounterParticipant',
      ),
      'appointment' => 
      array (
        'setter' => 'setAppointment',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'length' => 
      array (
        'setter' => 'setLength',
        'element' => 'Duration',
        'type' => 'FHIR_DSTU_TWO\\FHIRDuration',
      ),
      'reason' => 
      array (
        'setter' => 'addReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'indication' => 
      array (
        'setter' => 'addIndication',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'hospitalization' => 
      array (
        'setter' => 'setHospitalization',
        'element' => 'Encounter.Hospitalization',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIREncounter\\FHIREncounterHospitalization',
      ),
      'location' => 
      array (
        'setter' => 'addLocation',
        'element' => 'Encounter.Location',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIREncounter\\FHIREncounterLocation',
      ),
      'serviceProvider' => 
      array (
        'setter' => 'setServiceProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'partOf' => 
      array (
        'setter' => 'setPartOf',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Encounter.StatusHistory' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIREncounter\\FHIREncounterStatusHistory',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'EncounterState',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIREncounterState',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'Encounter.Participant' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIREncounter\\FHIREncounterParticipant',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'individual' => 
      array (
        'setter' => 'setIndividual',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Encounter.Hospitalization' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIREncounter\\FHIREncounterHospitalization',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'preAdmissionIdentifier' => 
      array (
        'setter' => 'setPreAdmissionIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'origin' => 
      array (
        'setter' => 'setOrigin',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'admitSource' => 
      array (
        'setter' => 'setAdmitSource',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'admittingDiagnosis' => 
      array (
        'setter' => 'addAdmittingDiagnosis',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'reAdmission' => 
      array (
        'setter' => 'setReAdmission',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'dietPreference' => 
      array (
        'setter' => 'addDietPreference',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'specialCourtesy' => 
      array (
        'setter' => 'addSpecialCourtesy',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'specialArrangement' => 
      array (
        'setter' => 'addSpecialArrangement',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'destination' => 
      array (
        'setter' => 'setDestination',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dischargeDisposition' => 
      array (
        'setter' => 'setDischargeDisposition',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'dischargeDiagnosis' => 
      array (
        'setter' => 'addDischargeDiagnosis',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Encounter.Location' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIREncounter\\FHIREncounterLocation',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'location' => 
      array (
        'setter' => 'setLocation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'EncounterLocationStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIREncounterLocationStatus',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'EncounterLocationStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIREncounterLocationStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'EncounterLocationStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'EncounterClass' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIREncounterClass',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'EncounterClass-list',
        'type' => 'string',
      ),
    ),
  ),
  'EncounterState' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIREncounterState',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'EncounterState-list',
        'type' => 'string',
      ),
    ),
  ),
  'EnrollmentRequest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREnrollmentRequest',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'provider' => 
      array (
        'setter' => 'setProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'coverage' => 
      array (
        'setter' => 'setCoverage',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'relationship' => 
      array (
        'setter' => 'setRelationship',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'EnrollmentResponse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREnrollmentResponse',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'outcome' => 
      array (
        'setter' => 'setOutcome',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'disposition' => 
      array (
        'setter' => 'setDisposition',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestProvider' => 
      array (
        'setter' => 'setRequestProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestOrganization' => 
      array (
        'setter' => 'setRequestOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'EpisodeOfCare' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIREpisodeOfCare',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'EpisodeOfCareStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIREpisodeOfCareStatus',
      ),
      'statusHistory' => 
      array (
        'setter' => 'addStatusHistory',
        'element' => 'EpisodeOfCare.StatusHistory',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIREpisodeOfCare\\FHIREpisodeOfCareStatusHistory',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'condition' => 
      array (
        'setter' => 'addCondition',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'managingOrganization' => 
      array (
        'setter' => 'setManagingOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'referralRequest' => 
      array (
        'setter' => 'addReferralRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'careManager' => 
      array (
        'setter' => 'setCareManager',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'careTeam' => 
      array (
        'setter' => 'addCareTeam',
        'element' => 'EpisodeOfCare.CareTeam',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIREpisodeOfCare\\FHIREpisodeOfCareCareTeam',
      ),
    ),
  ),
  'EpisodeOfCare.StatusHistory' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIREpisodeOfCare\\FHIREpisodeOfCareStatusHistory',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'EpisodeOfCareStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIREpisodeOfCareStatus',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'EpisodeOfCare.CareTeam' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIREpisodeOfCare\\FHIREpisodeOfCareCareTeam',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'role' => 
      array (
        'setter' => 'addRole',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'member' => 
      array (
        'setter' => 'setMember',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'EpisodeOfCareStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIREpisodeOfCareStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'EpisodeOfCareStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'ExplanationOfBenefit' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRExplanationOfBenefit',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'outcome' => 
      array (
        'setter' => 'setOutcome',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'disposition' => 
      array (
        'setter' => 'setDisposition',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestProvider' => 
      array (
        'setter' => 'setRequestProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestOrganization' => 
      array (
        'setter' => 'setRequestOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'FamilyMemberHistory' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRFamilyMemberHistory',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'FamilyHistoryStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRFamilyHistoryStatus',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'relationship' => 
      array (
        'setter' => 'setRelationship',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'gender' => 
      array (
        'setter' => 'setGender',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'bornPeriod' => 
      array (
        'setter' => 'setBornPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'bornDate' => 
      array (
        'setter' => 'setBornDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'bornString' => 
      array (
        'setter' => 'setBornString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'ageQuantity' => 
      array (
        'setter' => 'setAgeQuantity',
        'element' => 'Age',
        'type' => 'FHIR_DSTU_TWO\\FHIRAge',
      ),
      'ageRange' => 
      array (
        'setter' => 'setAgeRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'ageString' => 
      array (
        'setter' => 'setAgeString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'deceasedBoolean' => 
      array (
        'setter' => 'setDeceasedBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'deceasedQuantity' => 
      array (
        'setter' => 'setDeceasedQuantity',
        'element' => 'Age',
        'type' => 'FHIR_DSTU_TWO\\FHIRAge',
      ),
      'deceasedRange' => 
      array (
        'setter' => 'setDeceasedRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'deceasedDate' => 
      array (
        'setter' => 'setDeceasedDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'deceasedString' => 
      array (
        'setter' => 'setDeceasedString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'condition' => 
      array (
        'setter' => 'addCondition',
        'element' => 'FamilyMemberHistory.Condition',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRFamilyMemberHistory\\FHIRFamilyMemberHistoryCondition',
      ),
    ),
  ),
  'FamilyMemberHistory.Condition' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRFamilyMemberHistory\\FHIRFamilyMemberHistoryCondition',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'outcome' => 
      array (
        'setter' => 'setOutcome',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'onsetQuantity' => 
      array (
        'setter' => 'setOnsetQuantity',
        'element' => 'Age',
        'type' => 'FHIR_DSTU_TWO\\FHIRAge',
      ),
      'onsetRange' => 
      array (
        'setter' => 'setOnsetRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'onsetPeriod' => 
      array (
        'setter' => 'setOnsetPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'onsetString' => 
      array (
        'setter' => 'setOnsetString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
    ),
  ),
  'FamilyHistoryStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRFamilyHistoryStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'FamilyHistoryStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Flag' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRFlag',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'FlagStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRFlagStatus',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'FlagStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRFlagStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'FlagStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Goal' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRGoal',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'startDate' => 
      array (
        'setter' => 'setStartDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'startCodeableConcept' => 
      array (
        'setter' => 'setStartCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'targetDate' => 
      array (
        'setter' => 'setTargetDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'targetQuantity' => 
      array (
        'setter' => 'setTargetQuantity',
        'element' => 'Duration',
        'type' => 'FHIR_DSTU_TWO\\FHIRDuration',
      ),
      'category' => 
      array (
        'setter' => 'addCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'GoalStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRGoalStatus',
      ),
      'statusDate' => 
      array (
        'setter' => 'setStatusDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'statusReason' => 
      array (
        'setter' => 'setStatusReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'priority' => 
      array (
        'setter' => 'setPriority',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'addresses' => 
      array (
        'setter' => 'addAddresses',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'note' => 
      array (
        'setter' => 'addNote',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'outcome' => 
      array (
        'setter' => 'addOutcome',
        'element' => 'Goal.Outcome',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRGoal\\FHIRGoalOutcome',
      ),
    ),
  ),
  'Goal.Outcome' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRGoal\\FHIRGoalOutcome',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'resultCodeableConcept' => 
      array (
        'setter' => 'setResultCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'resultReference' => 
      array (
        'setter' => 'setResultReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'GoalStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRGoalStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'GoalStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Group' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRGroup',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'GroupType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRGroupType',
      ),
      'actual' => 
      array (
        'setter' => 'setActual',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'characteristic' => 
      array (
        'setter' => 'addCharacteristic',
        'element' => 'Group.Characteristic',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRGroup\\FHIRGroupCharacteristic',
      ),
      'member' => 
      array (
        'setter' => 'addMember',
        'element' => 'Group.Member',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRGroup\\FHIRGroupMember',
      ),
    ),
  ),
  'Group.Characteristic' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRGroup\\FHIRGroupCharacteristic',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'valueCodeableConcept' => 
      array (
        'setter' => 'setValueCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'valueBoolean' => 
      array (
        'setter' => 'setValueBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'valueQuantity' => 
      array (
        'setter' => 'setValueQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'valueRange' => 
      array (
        'setter' => 'setValueRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'exclude' => 
      array (
        'setter' => 'setExclude',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'Group.Member' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRGroup\\FHIRGroupMember',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'entity' => 
      array (
        'setter' => 'setEntity',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'inactive' => 
      array (
        'setter' => 'setInactive',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
    ),
  ),
  'GroupType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRGroupType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'GroupType-list',
        'type' => 'string',
      ),
    ),
  ),
  'HealthcareService' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRHealthcareService',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'providedBy' => 
      array (
        'setter' => 'setProvidedBy',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'serviceCategory' => 
      array (
        'setter' => 'setServiceCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'serviceType' => 
      array (
        'setter' => 'addServiceType',
        'element' => 'HealthcareService.ServiceType',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRHealthcareService\\FHIRHealthcareServiceServiceType',
      ),
      'location' => 
      array (
        'setter' => 'setLocation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'serviceName' => 
      array (
        'setter' => 'setServiceName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'comment' => 
      array (
        'setter' => 'setComment',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'extraDetails' => 
      array (
        'setter' => 'setExtraDetails',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'photo' => 
      array (
        'setter' => 'setPhoto',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'coverageArea' => 
      array (
        'setter' => 'addCoverageArea',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'serviceProvisionCode' => 
      array (
        'setter' => 'addServiceProvisionCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'eligibility' => 
      array (
        'setter' => 'setEligibility',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'eligibilityNote' => 
      array (
        'setter' => 'setEligibilityNote',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'programName' => 
      array (
        'setter' => 'addProgramName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'characteristic' => 
      array (
        'setter' => 'addCharacteristic',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'referralMethod' => 
      array (
        'setter' => 'addReferralMethod',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'publicKey' => 
      array (
        'setter' => 'setPublicKey',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'appointmentRequired' => 
      array (
        'setter' => 'setAppointmentRequired',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'availableTime' => 
      array (
        'setter' => 'addAvailableTime',
        'element' => 'HealthcareService.AvailableTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRHealthcareService\\FHIRHealthcareServiceAvailableTime',
      ),
      'notAvailable' => 
      array (
        'setter' => 'addNotAvailable',
        'element' => 'HealthcareService.NotAvailable',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRHealthcareService\\FHIRHealthcareServiceNotAvailable',
      ),
      'availabilityExceptions' => 
      array (
        'setter' => 'setAvailabilityExceptions',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'HealthcareService.ServiceType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRHealthcareService\\FHIRHealthcareServiceServiceType',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'specialty' => 
      array (
        'setter' => 'addSpecialty',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'HealthcareService.AvailableTime' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRHealthcareService\\FHIRHealthcareServiceAvailableTime',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'daysOfWeek' => 
      array (
        'setter' => 'addDaysOfWeek',
        'element' => 'DaysOfWeek',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDaysOfWeek',
      ),
      'allDay' => 
      array (
        'setter' => 'setAllDay',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'availableStartTime' => 
      array (
        'setter' => 'setAvailableStartTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'availableEndTime' => 
      array (
        'setter' => 'setAvailableEndTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
    ),
  ),
  'HealthcareService.NotAvailable' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRHealthcareService\\FHIRHealthcareServiceNotAvailable',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'during' => 
      array (
        'setter' => 'setDuring',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'DaysOfWeek' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDaysOfWeek',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DaysOfWeek-list',
        'type' => 'string',
      ),
    ),
  ),
  'ImagingObjectSelection' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRImagingObjectSelection',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'uid' => 
      array (
        'setter' => 'setUid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'title' => 
      array (
        'setter' => 'setTitle',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'authoringTime' => 
      array (
        'setter' => 'setAuthoringTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'study' => 
      array (
        'setter' => 'addStudy',
        'element' => 'ImagingObjectSelection.Study',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingObjectSelection\\FHIRImagingObjectSelectionStudy',
      ),
    ),
  ),
  'ImagingObjectSelection.Study' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingObjectSelection\\FHIRImagingObjectSelectionStudy',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'uid' => 
      array (
        'setter' => 'setUid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'imagingStudy' => 
      array (
        'setter' => 'setImagingStudy',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'series' => 
      array (
        'setter' => 'addSeries',
        'element' => 'ImagingObjectSelection.Series',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingObjectSelection\\FHIRImagingObjectSelectionSeries',
      ),
    ),
  ),
  'ImagingObjectSelection.Series' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingObjectSelection\\FHIRImagingObjectSelectionSeries',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'uid' => 
      array (
        'setter' => 'setUid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'instance' => 
      array (
        'setter' => 'addInstance',
        'element' => 'ImagingObjectSelection.Instance',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingObjectSelection\\FHIRImagingObjectSelectionInstance',
      ),
    ),
  ),
  'ImagingObjectSelection.Instance' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingObjectSelection\\FHIRImagingObjectSelectionInstance',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sopClass' => 
      array (
        'setter' => 'setSopClass',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'uid' => 
      array (
        'setter' => 'setUid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'frames' => 
      array (
        'setter' => 'addFrames',
        'element' => 'ImagingObjectSelection.Frames',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingObjectSelection\\FHIRImagingObjectSelectionFrames',
      ),
    ),
  ),
  'ImagingObjectSelection.Frames' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingObjectSelection\\FHIRImagingObjectSelectionFrames',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'frameNumbers' => 
      array (
        'setter' => 'addFrameNumbers',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
    ),
  ),
  'ImagingStudy' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRImagingStudy',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'started' => 
      array (
        'setter' => 'setStarted',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'uid' => 
      array (
        'setter' => 'setUid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'accession' => 
      array (
        'setter' => 'setAccession',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'order' => 
      array (
        'setter' => 'addOrder',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'modalityList' => 
      array (
        'setter' => 'addModalityList',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'referrer' => 
      array (
        'setter' => 'setReferrer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'availability' => 
      array (
        'setter' => 'setAvailability',
        'element' => 'InstanceAvailability',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstanceAvailability',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'numberOfSeries' => 
      array (
        'setter' => 'setNumberOfSeries',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'numberOfInstances' => 
      array (
        'setter' => 'setNumberOfInstances',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'procedure' => 
      array (
        'setter' => 'addProcedure',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'interpreter' => 
      array (
        'setter' => 'setInterpreter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'series' => 
      array (
        'setter' => 'addSeries',
        'element' => 'ImagingStudy.Series',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingStudy\\FHIRImagingStudySeries',
      ),
    ),
  ),
  'ImagingStudy.Series' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingStudy\\FHIRImagingStudySeries',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'number' => 
      array (
        'setter' => 'setNumber',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'modality' => 
      array (
        'setter' => 'setModality',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'uid' => 
      array (
        'setter' => 'setUid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'numberOfInstances' => 
      array (
        'setter' => 'setNumberOfInstances',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'availability' => 
      array (
        'setter' => 'setAvailability',
        'element' => 'InstanceAvailability',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstanceAvailability',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'bodySite' => 
      array (
        'setter' => 'setBodySite',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'laterality' => 
      array (
        'setter' => 'setLaterality',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'started' => 
      array (
        'setter' => 'setStarted',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'instance' => 
      array (
        'setter' => 'addInstance',
        'element' => 'ImagingStudy.Instance',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingStudy\\FHIRImagingStudyInstance',
      ),
    ),
  ),
  'ImagingStudy.Instance' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImagingStudy\\FHIRImagingStudyInstance',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'number' => 
      array (
        'setter' => 'setNumber',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'uid' => 
      array (
        'setter' => 'setUid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'sopClass' => 
      array (
        'setter' => 'setSopClass',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'title' => 
      array (
        'setter' => 'setTitle',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'content' => 
      array (
        'setter' => 'addContent',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
    ),
  ),
  'InstanceAvailability' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRInstanceAvailability',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'InstanceAvailability-list',
        'type' => 'string',
      ),
    ),
  ),
  'Immunization' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRImmunization',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'vaccineCode' => 
      array (
        'setter' => 'setVaccineCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'wasNotGiven' => 
      array (
        'setter' => 'setWasNotGiven',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'reported' => 
      array (
        'setter' => 'setReported',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'performer' => 
      array (
        'setter' => 'setPerformer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requester' => 
      array (
        'setter' => 'setRequester',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'manufacturer' => 
      array (
        'setter' => 'setManufacturer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'location' => 
      array (
        'setter' => 'setLocation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'lotNumber' => 
      array (
        'setter' => 'setLotNumber',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'expirationDate' => 
      array (
        'setter' => 'setExpirationDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'site' => 
      array (
        'setter' => 'setSite',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'route' => 
      array (
        'setter' => 'setRoute',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'doseQuantity' => 
      array (
        'setter' => 'setDoseQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'note' => 
      array (
        'setter' => 'addNote',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'explanation' => 
      array (
        'setter' => 'setExplanation',
        'element' => 'Immunization.Explanation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunization\\FHIRImmunizationExplanation',
      ),
      'reaction' => 
      array (
        'setter' => 'addReaction',
        'element' => 'Immunization.Reaction',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunization\\FHIRImmunizationReaction',
      ),
      'vaccinationProtocol' => 
      array (
        'setter' => 'addVaccinationProtocol',
        'element' => 'Immunization.VaccinationProtocol',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunization\\FHIRImmunizationVaccinationProtocol',
      ),
    ),
  ),
  'Immunization.Explanation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunization\\FHIRImmunizationExplanation',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'reason' => 
      array (
        'setter' => 'addReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonNotGiven' => 
      array (
        'setter' => 'addReasonNotGiven',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'Immunization.Reaction' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunization\\FHIRImmunizationReaction',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'detail' => 
      array (
        'setter' => 'setDetail',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'reported' => 
      array (
        'setter' => 'setReported',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
    ),
  ),
  'Immunization.VaccinationProtocol' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunization\\FHIRImmunizationVaccinationProtocol',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'doseSequence' => 
      array (
        'setter' => 'setDoseSequence',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'authority' => 
      array (
        'setter' => 'setAuthority',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'series' => 
      array (
        'setter' => 'setSeries',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'seriesDoses' => 
      array (
        'setter' => 'setSeriesDoses',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'targetDisease' => 
      array (
        'setter' => 'addTargetDisease',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'doseStatus' => 
      array (
        'setter' => 'setDoseStatus',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'doseStatusReason' => 
      array (
        'setter' => 'setDoseStatusReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'ImmunizationRecommendation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRImmunizationRecommendation',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'recommendation' => 
      array (
        'setter' => 'addRecommendation',
        'element' => 'ImmunizationRecommendation.Recommendation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunizationRecommendation\\FHIRImmunizationRecommendationRecommendation',
      ),
    ),
  ),
  'ImmunizationRecommendation.Recommendation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunizationRecommendation\\FHIRImmunizationRecommendationRecommendation',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'vaccineCode' => 
      array (
        'setter' => 'setVaccineCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'doseNumber' => 
      array (
        'setter' => 'setDoseNumber',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'forecastStatus' => 
      array (
        'setter' => 'setForecastStatus',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'dateCriterion' => 
      array (
        'setter' => 'addDateCriterion',
        'element' => 'ImmunizationRecommendation.DateCriterion',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunizationRecommendation\\FHIRImmunizationRecommendationDateCriterion',
      ),
      'protocol' => 
      array (
        'setter' => 'setProtocol',
        'element' => 'ImmunizationRecommendation.Protocol',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunizationRecommendation\\FHIRImmunizationRecommendationProtocol',
      ),
      'supportingImmunization' => 
      array (
        'setter' => 'addSupportingImmunization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'supportingPatientInformation' => 
      array (
        'setter' => 'addSupportingPatientInformation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ImmunizationRecommendation.DateCriterion' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunizationRecommendation\\FHIRImmunizationRecommendationDateCriterion',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
    ),
  ),
  'ImmunizationRecommendation.Protocol' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImmunizationRecommendation\\FHIRImmunizationRecommendationProtocol',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'doseSequence' => 
      array (
        'setter' => 'setDoseSequence',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'authority' => 
      array (
        'setter' => 'setAuthority',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'series' => 
      array (
        'setter' => 'setSeries',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'ImplementationGuide' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRImplementationGuide',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'experimental' => 
      array (
        'setter' => 'setExperimental',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'ImplementationGuide.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuideContact',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'useContext' => 
      array (
        'setter' => 'addUseContext',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'copyright' => 
      array (
        'setter' => 'setCopyright',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'fhirVersion' => 
      array (
        'setter' => 'setFhirVersion',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'dependency' => 
      array (
        'setter' => 'addDependency',
        'element' => 'ImplementationGuide.Dependency',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuideDependency',
      ),
      'package' => 
      array (
        'setter' => 'addPackage',
        'element' => 'ImplementationGuide.Package',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuidePackage',
      ),
      'global' => 
      array (
        'setter' => 'addGlobal',
        'element' => 'ImplementationGuide.Global',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuideGlobal',
      ),
      'binary' => 
      array (
        'setter' => 'addBinary',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'page' => 
      array (
        'setter' => 'setPage',
        'element' => 'ImplementationGuide.Page',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuidePage',
      ),
    ),
  ),
  'ImplementationGuide.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuideContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'ImplementationGuide.Dependency' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuideDependency',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'GuideDependencyType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRGuideDependencyType',
      ),
      'uri' => 
      array (
        'setter' => 'setUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
    ),
  ),
  'ImplementationGuide.Package' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuidePackage',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'resource' => 
      array (
        'setter' => 'addResource',
        'element' => 'ImplementationGuide.Resource',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuideResource',
      ),
    ),
  ),
  'ImplementationGuide.Resource' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuideResource',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'purpose' => 
      array (
        'setter' => 'setPurpose',
        'element' => 'GuideResourcePurpose',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRGuideResourcePurpose',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'acronym' => 
      array (
        'setter' => 'setAcronym',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'sourceUri' => 
      array (
        'setter' => 'setSourceUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'sourceReference' => 
      array (
        'setter' => 'setSourceReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'exampleFor' => 
      array (
        'setter' => 'setExampleFor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ImplementationGuide.Global' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuideGlobal',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'profile' => 
      array (
        'setter' => 'setProfile',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ImplementationGuide.Page' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuidePage',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'kind' => 
      array (
        'setter' => 'setKind',
        'element' => 'GuidePageKind',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRGuidePageKind',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'package' => 
      array (
        'setter' => 'addPackage',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'format' => 
      array (
        'setter' => 'setFormat',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'page' => 
      array (
        'setter' => 'addPage',
        'element' => 'ImplementationGuide.Page',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRImplementationGuide\\FHIRImplementationGuidePage',
      ),
    ),
  ),
  'GuideDependencyType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRGuideDependencyType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'GuideDependencyType-list',
        'type' => 'string',
      ),
    ),
  ),
  'GuideResourcePurpose' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRGuideResourcePurpose',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'GuideResourcePurpose-list',
        'type' => 'string',
      ),
    ),
  ),
  'GuidePageKind' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRGuidePageKind',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'GuidePageKind-list',
        'type' => 'string',
      ),
    ),
  ),
  'List' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRList',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'title' => 
      array (
        'setter' => 'setTitle',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'ListStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRListStatus',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'orderedBy' => 
      array (
        'setter' => 'setOrderedBy',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'mode' => 
      array (
        'setter' => 'setMode',
        'element' => 'ListMode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRListMode',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'entry' => 
      array (
        'setter' => 'addEntry',
        'element' => 'List.Entry',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRList\\FHIRListEntry',
      ),
      'emptyReason' => 
      array (
        'setter' => 'setEmptyReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'List.Entry' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRList\\FHIRListEntry',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'flag' => 
      array (
        'setter' => 'setFlag',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'deleted' => 
      array (
        'setter' => 'setDeleted',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'item' => 
      array (
        'setter' => 'setItem',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ListStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRListStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ListStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'ListMode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRListMode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ListMode-list',
        'type' => 'string',
      ),
    ),
  ),
  'Location' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRLocation',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'LocationStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRLocationStatus',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'mode' => 
      array (
        'setter' => 'setMode',
        'element' => 'LocationMode',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRLocationMode',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'address' => 
      array (
        'setter' => 'setAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'physicalType' => 
      array (
        'setter' => 'setPhysicalType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'position' => 
      array (
        'setter' => 'setPosition',
        'element' => 'Location.Position',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRLocation\\FHIRLocationPosition',
      ),
      'managingOrganization' => 
      array (
        'setter' => 'setManagingOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'partOf' => 
      array (
        'setter' => 'setPartOf',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Location.Position' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRLocation\\FHIRLocationPosition',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'longitude' => 
      array (
        'setter' => 'setLongitude',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'latitude' => 
      array (
        'setter' => 'setLatitude',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'altitude' => 
      array (
        'setter' => 'setAltitude',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
    ),
  ),
  'LocationStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRLocationStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'LocationStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'LocationMode' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRLocationMode',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'LocationMode-list',
        'type' => 'string',
      ),
    ),
  ),
  'Media' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedia',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'DigitalMediaType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDigitalMediaType',
      ),
      'subtype' => 
      array (
        'setter' => 'setSubtype',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'operator' => 
      array (
        'setter' => 'setOperator',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'view' => 
      array (
        'setter' => 'setView',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'deviceName' => 
      array (
        'setter' => 'setDeviceName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'height' => 
      array (
        'setter' => 'setHeight',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'width' => 
      array (
        'setter' => 'setWidth',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'frames' => 
      array (
        'setter' => 'setFrames',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'duration' => 
      array (
        'setter' => 'setDuration',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'content' => 
      array (
        'setter' => 'setContent',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
    ),
  ),
  'DigitalMediaType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRDigitalMediaType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'DigitalMediaType-list',
        'type' => 'string',
      ),
    ),
  ),
  'Medication' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedication',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'isBrand' => 
      array (
        'setter' => 'setIsBrand',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'manufacturer' => 
      array (
        'setter' => 'setManufacturer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'product' => 
      array (
        'setter' => 'setProduct',
        'element' => 'Medication.Product',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedication\\FHIRMedicationProduct',
      ),
      'package' => 
      array (
        'setter' => 'setPackage',
        'element' => 'Medication.Package',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedication\\FHIRMedicationPackage',
      ),
    ),
  ),
  'Medication.Product' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedication\\FHIRMedicationProduct',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'form' => 
      array (
        'setter' => 'setForm',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'ingredient' => 
      array (
        'setter' => 'addIngredient',
        'element' => 'Medication.Ingredient',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedication\\FHIRMedicationIngredient',
      ),
      'batch' => 
      array (
        'setter' => 'addBatch',
        'element' => 'Medication.Batch',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedication\\FHIRMedicationBatch',
      ),
    ),
  ),
  'Medication.Ingredient' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedication\\FHIRMedicationIngredient',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'item' => 
      array (
        'setter' => 'setItem',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'amount' => 
      array (
        'setter' => 'setAmount',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
    ),
  ),
  'Medication.Batch' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedication\\FHIRMedicationBatch',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'lotNumber' => 
      array (
        'setter' => 'setLotNumber',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'expirationDate' => 
      array (
        'setter' => 'setExpirationDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
    ),
  ),
  'Medication.Package' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedication\\FHIRMedicationPackage',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'container' => 
      array (
        'setter' => 'setContainer',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'content' => 
      array (
        'setter' => 'addContent',
        'element' => 'Medication.Content',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedication\\FHIRMedicationContent',
      ),
    ),
  ),
  'Medication.Content' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedication\\FHIRMedicationContent',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'item' => 
      array (
        'setter' => 'setItem',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'amount' => 
      array (
        'setter' => 'setAmount',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
    ),
  ),
  'MedicationAdministration' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedicationAdministration',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'MedicationAdministrationStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMedicationAdministrationStatus',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'practitioner' => 
      array (
        'setter' => 'setPractitioner',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'prescription' => 
      array (
        'setter' => 'setPrescription',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'wasNotGiven' => 
      array (
        'setter' => 'setWasNotGiven',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'reasonNotGiven' => 
      array (
        'setter' => 'addReasonNotGiven',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonGiven' => 
      array (
        'setter' => 'addReasonGiven',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'effectiveTimeDateTime' => 
      array (
        'setter' => 'setEffectiveTimeDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'effectiveTimePeriod' => 
      array (
        'setter' => 'setEffectiveTimePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'medicationCodeableConcept' => 
      array (
        'setter' => 'setMedicationCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'medicationReference' => 
      array (
        'setter' => 'setMedicationReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'device' => 
      array (
        'setter' => 'addDevice',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'dosage' => 
      array (
        'setter' => 'setDosage',
        'element' => 'MedicationAdministration.Dosage',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationAdministration\\FHIRMedicationAdministrationDosage',
      ),
    ),
  ),
  'MedicationAdministration.Dosage' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationAdministration\\FHIRMedicationAdministrationDosage',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'siteCodeableConcept' => 
      array (
        'setter' => 'setSiteCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'siteReference' => 
      array (
        'setter' => 'setSiteReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'route' => 
      array (
        'setter' => 'setRoute',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'method' => 
      array (
        'setter' => 'setMethod',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'rateRatio' => 
      array (
        'setter' => 'setRateRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'rateRange' => 
      array (
        'setter' => 'setRateRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
    ),
  ),
  'MedicationAdministrationStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRMedicationAdministrationStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'MedicationAdministrationStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'MedicationDispense' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedicationDispense',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'MedicationDispenseStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMedicationDispenseStatus',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dispenser' => 
      array (
        'setter' => 'setDispenser',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'authorizingPrescription' => 
      array (
        'setter' => 'addAuthorizingPrescription',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'daysSupply' => 
      array (
        'setter' => 'setDaysSupply',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'medicationCodeableConcept' => 
      array (
        'setter' => 'setMedicationCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'medicationReference' => 
      array (
        'setter' => 'setMedicationReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'whenPrepared' => 
      array (
        'setter' => 'setWhenPrepared',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'whenHandedOver' => 
      array (
        'setter' => 'setWhenHandedOver',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'destination' => 
      array (
        'setter' => 'setDestination',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'receiver' => 
      array (
        'setter' => 'addReceiver',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'dosageInstruction' => 
      array (
        'setter' => 'addDosageInstruction',
        'element' => 'MedicationDispense.DosageInstruction',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationDispense\\FHIRMedicationDispenseDosageInstruction',
      ),
      'substitution' => 
      array (
        'setter' => 'setSubstitution',
        'element' => 'MedicationDispense.Substitution',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationDispense\\FHIRMedicationDispenseSubstitution',
      ),
    ),
  ),
  'MedicationDispense.DosageInstruction' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationDispense\\FHIRMedicationDispenseDosageInstruction',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'additionalInstructions' => 
      array (
        'setter' => 'setAdditionalInstructions',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'timing' => 
      array (
        'setter' => 'setTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'asNeededBoolean' => 
      array (
        'setter' => 'setAsNeededBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'asNeededCodeableConcept' => 
      array (
        'setter' => 'setAsNeededCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'siteCodeableConcept' => 
      array (
        'setter' => 'setSiteCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'siteReference' => 
      array (
        'setter' => 'setSiteReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'route' => 
      array (
        'setter' => 'setRoute',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'method' => 
      array (
        'setter' => 'setMethod',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'doseRange' => 
      array (
        'setter' => 'setDoseRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'doseQuantity' => 
      array (
        'setter' => 'setDoseQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'rateRatio' => 
      array (
        'setter' => 'setRateRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'rateRange' => 
      array (
        'setter' => 'setRateRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'maxDosePerPeriod' => 
      array (
        'setter' => 'setMaxDosePerPeriod',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
    ),
  ),
  'MedicationDispense.Substitution' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationDispense\\FHIRMedicationDispenseSubstitution',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reason' => 
      array (
        'setter' => 'addReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'responsibleParty' => 
      array (
        'setter' => 'addResponsibleParty',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'MedicationDispenseStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRMedicationDispenseStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'MedicationDispenseStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'MedicationOrder' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedicationOrder',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'dateWritten' => 
      array (
        'setter' => 'setDateWritten',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'MedicationOrderStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMedicationOrderStatus',
      ),
      'dateEnded' => 
      array (
        'setter' => 'setDateEnded',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'reasonEnded' => 
      array (
        'setter' => 'setReasonEnded',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'prescriber' => 
      array (
        'setter' => 'setPrescriber',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'reasonCodeableConcept' => 
      array (
        'setter' => 'setReasonCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonReference' => 
      array (
        'setter' => 'setReasonReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'medicationCodeableConcept' => 
      array (
        'setter' => 'setMedicationCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'medicationReference' => 
      array (
        'setter' => 'setMedicationReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dosageInstruction' => 
      array (
        'setter' => 'addDosageInstruction',
        'element' => 'MedicationOrder.DosageInstruction',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationOrder\\FHIRMedicationOrderDosageInstruction',
      ),
      'dispenseRequest' => 
      array (
        'setter' => 'setDispenseRequest',
        'element' => 'MedicationOrder.DispenseRequest',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationOrder\\FHIRMedicationOrderDispenseRequest',
      ),
      'substitution' => 
      array (
        'setter' => 'setSubstitution',
        'element' => 'MedicationOrder.Substitution',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationOrder\\FHIRMedicationOrderSubstitution',
      ),
      'priorPrescription' => 
      array (
        'setter' => 'setPriorPrescription',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'MedicationOrder.DosageInstruction' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationOrder\\FHIRMedicationOrderDosageInstruction',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'additionalInstructions' => 
      array (
        'setter' => 'setAdditionalInstructions',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'timing' => 
      array (
        'setter' => 'setTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'asNeededBoolean' => 
      array (
        'setter' => 'setAsNeededBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'asNeededCodeableConcept' => 
      array (
        'setter' => 'setAsNeededCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'siteCodeableConcept' => 
      array (
        'setter' => 'setSiteCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'siteReference' => 
      array (
        'setter' => 'setSiteReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'route' => 
      array (
        'setter' => 'setRoute',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'method' => 
      array (
        'setter' => 'setMethod',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'doseRange' => 
      array (
        'setter' => 'setDoseRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'doseQuantity' => 
      array (
        'setter' => 'setDoseQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'rateRatio' => 
      array (
        'setter' => 'setRateRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'rateRange' => 
      array (
        'setter' => 'setRateRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'maxDosePerPeriod' => 
      array (
        'setter' => 'setMaxDosePerPeriod',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
    ),
  ),
  'MedicationOrder.DispenseRequest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationOrder\\FHIRMedicationOrderDispenseRequest',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'medicationCodeableConcept' => 
      array (
        'setter' => 'setMedicationCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'medicationReference' => 
      array (
        'setter' => 'setMedicationReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'validityPeriod' => 
      array (
        'setter' => 'setValidityPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'numberOfRepeatsAllowed' => 
      array (
        'setter' => 'setNumberOfRepeatsAllowed',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'expectedSupplyDuration' => 
      array (
        'setter' => 'setExpectedSupplyDuration',
        'element' => 'Duration',
        'type' => 'FHIR_DSTU_TWO\\FHIRDuration',
      ),
    ),
  ),
  'MedicationOrder.Substitution' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationOrder\\FHIRMedicationOrderSubstitution',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reason' => 
      array (
        'setter' => 'setReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'MedicationOrderStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRMedicationOrderStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'MedicationOrderStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'MedicationPrescription' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedicationPrescription',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'dateWritten' => 
      array (
        'setter' => 'setDateWritten',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'MedicationPrescriptionStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMedicationPrescriptionStatus',
      ),
      'dateEnded' => 
      array (
        'setter' => 'setDateEnded',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'reasonEnded' => 
      array (
        'setter' => 'setReasonEnded',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'prescriber' => 
      array (
        'setter' => 'setPrescriber',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'reasonCodeableConcept' => 
      array (
        'setter' => 'setReasonCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonReference' => 
      array (
        'setter' => 'setReasonReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'medicationCodeableConcept' => 
      array (
        'setter' => 'setMedicationCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'medicationReference' => 
      array (
        'setter' => 'setMedicationReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dosageInstruction' => 
      array (
        'setter' => 'addDosageInstruction',
        'element' => 'MedicationPrescription.DosageInstruction',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationPrescription\\FHIRMedicationPrescriptionDosageInstruction',
      ),
      'dispense' => 
      array (
        'setter' => 'setDispense',
        'element' => 'MedicationPrescription.Dispense',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationPrescription\\FHIRMedicationPrescriptionDispense',
      ),
      'substitution' => 
      array (
        'setter' => 'setSubstitution',
        'element' => 'MedicationPrescription.Substitution',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationPrescription\\FHIRMedicationPrescriptionSubstitution',
      ),
      'priorPrescription' => 
      array (
        'setter' => 'setPriorPrescription',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'MedicationPrescription.DosageInstruction' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationPrescription\\FHIRMedicationPrescriptionDosageInstruction',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'additionalInstructions' => 
      array (
        'setter' => 'setAdditionalInstructions',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'timing' => 
      array (
        'setter' => 'setTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'asNeededBoolean' => 
      array (
        'setter' => 'setAsNeededBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'asNeededCodeableConcept' => 
      array (
        'setter' => 'setAsNeededCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'site' => 
      array (
        'setter' => 'setSite',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'route' => 
      array (
        'setter' => 'setRoute',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'method' => 
      array (
        'setter' => 'setMethod',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'doseRange' => 
      array (
        'setter' => 'setDoseRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'doseQuantity' => 
      array (
        'setter' => 'setDoseQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'rateRatio' => 
      array (
        'setter' => 'setRateRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'rateQuantity' => 
      array (
        'setter' => 'setRateQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'rateRange' => 
      array (
        'setter' => 'setRateRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'maxDosePerPeriod' => 
      array (
        'setter' => 'setMaxDosePerPeriod',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
    ),
  ),
  'MedicationPrescription.Dispense' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationPrescription\\FHIRMedicationPrescriptionDispense',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'medicationCodeableConcept' => 
      array (
        'setter' => 'setMedicationCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'medicationReference' => 
      array (
        'setter' => 'setMedicationReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'validityPeriod' => 
      array (
        'setter' => 'setValidityPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'numberOfRepeatsAllowed' => 
      array (
        'setter' => 'setNumberOfRepeatsAllowed',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'expectedSupplyDuration' => 
      array (
        'setter' => 'setExpectedSupplyDuration',
        'element' => 'Duration',
        'type' => 'FHIR_DSTU_TWO\\FHIRDuration',
      ),
    ),
  ),
  'MedicationPrescription.Substitution' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationPrescription\\FHIRMedicationPrescriptionSubstitution',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reason' => 
      array (
        'setter' => 'setReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'MedicationPrescriptionStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRMedicationPrescriptionStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'MedicationPrescriptionStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'MedicationStatement' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMedicationStatement',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'informationSource' => 
      array (
        'setter' => 'setInformationSource',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dateAsserted' => 
      array (
        'setter' => 'setDateAsserted',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'MedicationStatementStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMedicationStatementStatus',
      ),
      'wasNotTaken' => 
      array (
        'setter' => 'setWasNotTaken',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'reasonNotTaken' => 
      array (
        'setter' => 'addReasonNotTaken',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonForUseCodeableConcept' => 
      array (
        'setter' => 'setReasonForUseCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonForUseReference' => 
      array (
        'setter' => 'setReasonForUseReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'effectiveDateTime' => 
      array (
        'setter' => 'setEffectiveDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'effectivePeriod' => 
      array (
        'setter' => 'setEffectivePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'note' => 
      array (
        'setter' => 'setNote',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'supportingInformation' => 
      array (
        'setter' => 'addSupportingInformation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'medicationCodeableConcept' => 
      array (
        'setter' => 'setMedicationCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'medicationReference' => 
      array (
        'setter' => 'setMedicationReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dosage' => 
      array (
        'setter' => 'addDosage',
        'element' => 'MedicationStatement.Dosage',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationStatement\\FHIRMedicationStatementDosage',
      ),
    ),
  ),
  'MedicationStatement.Dosage' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMedicationStatement\\FHIRMedicationStatementDosage',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'timing' => 
      array (
        'setter' => 'setTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'asNeededBoolean' => 
      array (
        'setter' => 'setAsNeededBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'asNeededCodeableConcept' => 
      array (
        'setter' => 'setAsNeededCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'siteCodeableConcept' => 
      array (
        'setter' => 'setSiteCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'siteReference' => 
      array (
        'setter' => 'setSiteReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'route' => 
      array (
        'setter' => 'setRoute',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'method' => 
      array (
        'setter' => 'setMethod',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'quantityQuantity' => 
      array (
        'setter' => 'setQuantityQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'quantityRange' => 
      array (
        'setter' => 'setQuantityRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'rateRatio' => 
      array (
        'setter' => 'setRateRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'rateRange' => 
      array (
        'setter' => 'setRateRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'maxDosePerPeriod' => 
      array (
        'setter' => 'setMaxDosePerPeriod',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
    ),
  ),
  'MedicationStatementStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRMedicationStatementStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'MedicationStatementStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'MessageHeader' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRMessageHeader',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'timestamp' => 
      array (
        'setter' => 'setTimestamp',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'event' => 
      array (
        'setter' => 'setEvent',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'response' => 
      array (
        'setter' => 'setResponse',
        'element' => 'MessageHeader.Response',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMessageHeader\\FHIRMessageHeaderResponse',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'MessageHeader.Source',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMessageHeader\\FHIRMessageHeaderSource',
      ),
      'destination' => 
      array (
        'setter' => 'addDestination',
        'element' => 'MessageHeader.Destination',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRMessageHeader\\FHIRMessageHeaderDestination',
      ),
      'enterer' => 
      array (
        'setter' => 'setEnterer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'receiver' => 
      array (
        'setter' => 'setReceiver',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'responsible' => 
      array (
        'setter' => 'setResponsible',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'reason' => 
      array (
        'setter' => 'setReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'data' => 
      array (
        'setter' => 'addData',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'MessageHeader.Response' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMessageHeader\\FHIRMessageHeaderResponse',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'ResponseType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRResponseType',
      ),
      'details' => 
      array (
        'setter' => 'setDetails',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'MessageHeader.Source' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMessageHeader\\FHIRMessageHeaderSource',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'software' => 
      array (
        'setter' => 'setSoftware',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'setContact',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'endpoint' => 
      array (
        'setter' => 'setEndpoint',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
    ),
  ),
  'MessageHeader.Destination' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRMessageHeader\\FHIRMessageHeaderDestination',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'endpoint' => 
      array (
        'setter' => 'setEndpoint',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
    ),
  ),
  'ResponseType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRResponseType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ResponseType-list',
        'type' => 'string',
      ),
    ),
  ),
  'NamingSystem' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRNamingSystem',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'kind' => 
      array (
        'setter' => 'setKind',
        'element' => 'NamingSystemType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNamingSystemType',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'NamingSystem.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRNamingSystem\\FHIRNamingSystemContact',
      ),
      'responsible' => 
      array (
        'setter' => 'setResponsible',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'useContext' => 
      array (
        'setter' => 'addUseContext',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'usage' => 
      array (
        'setter' => 'setUsage',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'uniqueId' => 
      array (
        'setter' => 'addUniqueId',
        'element' => 'NamingSystem.UniqueId',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRNamingSystem\\FHIRNamingSystemUniqueId',
      ),
      'replacedBy' => 
      array (
        'setter' => 'setReplacedBy',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'NamingSystem.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRNamingSystem\\FHIRNamingSystemContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'NamingSystem.UniqueId' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRNamingSystem\\FHIRNamingSystemUniqueId',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'NamingSystemIdentifierType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNamingSystemIdentifierType',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'preferred' => 
      array (
        'setter' => 'setPreferred',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'NamingSystemType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRNamingSystemType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'NamingSystemType-list',
        'type' => 'string',
      ),
    ),
  ),
  'NamingSystemIdentifierType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRNamingSystemIdentifierType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'NamingSystemIdentifierType-list',
        'type' => 'string',
      ),
    ),
  ),
  'NutritionOrder' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRNutritionOrder',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'orderer' => 
      array (
        'setter' => 'setOrderer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dateTime' => 
      array (
        'setter' => 'setDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'NutritionOrderStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNutritionOrderStatus',
      ),
      'allergyIntolerance' => 
      array (
        'setter' => 'addAllergyIntolerance',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'foodPreferenceModifier' => 
      array (
        'setter' => 'addFoodPreferenceModifier',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'excludeFoodModifier' => 
      array (
        'setter' => 'addExcludeFoodModifier',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'oralDiet' => 
      array (
        'setter' => 'setOralDiet',
        'element' => 'NutritionOrder.OralDiet',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderOralDiet',
      ),
      'supplement' => 
      array (
        'setter' => 'addSupplement',
        'element' => 'NutritionOrder.Supplement',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderSupplement',
      ),
      'enteralFormula' => 
      array (
        'setter' => 'setEnteralFormula',
        'element' => 'NutritionOrder.EnteralFormula',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderEnteralFormula',
      ),
    ),
  ),
  'NutritionOrder.OralDiet' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderOralDiet',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'schedule' => 
      array (
        'setter' => 'addSchedule',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'nutrient' => 
      array (
        'setter' => 'addNutrient',
        'element' => 'NutritionOrder.Nutrient',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderNutrient',
      ),
      'texture' => 
      array (
        'setter' => 'addTexture',
        'element' => 'NutritionOrder.Texture',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderTexture',
      ),
      'fluidConsistencyType' => 
      array (
        'setter' => 'addFluidConsistencyType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'instruction' => 
      array (
        'setter' => 'setInstruction',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'NutritionOrder.Nutrient' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderNutrient',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifier' => 
      array (
        'setter' => 'setModifier',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'amount' => 
      array (
        'setter' => 'setAmount',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
    ),
  ),
  'NutritionOrder.Texture' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderTexture',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifier' => 
      array (
        'setter' => 'setModifier',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'foodType' => 
      array (
        'setter' => 'setFoodType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'NutritionOrder.Supplement' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderSupplement',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'productName' => 
      array (
        'setter' => 'setProductName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'schedule' => 
      array (
        'setter' => 'addSchedule',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'instruction' => 
      array (
        'setter' => 'setInstruction',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'NutritionOrder.EnteralFormula' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderEnteralFormula',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'baseFormulaType' => 
      array (
        'setter' => 'setBaseFormulaType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'baseFormulaProductName' => 
      array (
        'setter' => 'setBaseFormulaProductName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'additiveType' => 
      array (
        'setter' => 'setAdditiveType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'additiveProductName' => 
      array (
        'setter' => 'setAdditiveProductName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'caloricDensity' => 
      array (
        'setter' => 'setCaloricDensity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'routeofAdministration' => 
      array (
        'setter' => 'setRouteofAdministration',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'administration' => 
      array (
        'setter' => 'addAdministration',
        'element' => 'NutritionOrder.Administration',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderAdministration',
      ),
      'maxVolumeToDeliver' => 
      array (
        'setter' => 'setMaxVolumeToDeliver',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'administrationInstruction' => 
      array (
        'setter' => 'setAdministrationInstruction',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'NutritionOrder.Administration' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRNutritionOrder\\FHIRNutritionOrderAdministration',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'schedule' => 
      array (
        'setter' => 'setSchedule',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'rateQuantity' => 
      array (
        'setter' => 'setRateQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'rateRatio' => 
      array (
        'setter' => 'setRateRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
    ),
  ),
  'NutritionOrderStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRNutritionOrderStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'NutritionOrderStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Observation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRObservation',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'ObservationStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRObservationStatus',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'effectiveDateTime' => 
      array (
        'setter' => 'setEffectiveDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'effectivePeriod' => 
      array (
        'setter' => 'setEffectivePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'issued' => 
      array (
        'setter' => 'setIssued',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'performer' => 
      array (
        'setter' => 'addPerformer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'valueQuantity' => 
      array (
        'setter' => 'setValueQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'valueCodeableConcept' => 
      array (
        'setter' => 'setValueCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'valueString' => 
      array (
        'setter' => 'setValueString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valueRange' => 
      array (
        'setter' => 'setValueRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'valueRatio' => 
      array (
        'setter' => 'setValueRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'valueSampledData' => 
      array (
        'setter' => 'setValueSampledData',
        'element' => 'SampledData',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
      ),
      'valueAttachment' => 
      array (
        'setter' => 'setValueAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'valueTime' => 
      array (
        'setter' => 'setValueTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'valueDateTime' => 
      array (
        'setter' => 'setValueDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'valuePeriod' => 
      array (
        'setter' => 'setValuePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'dataAbsentReason' => 
      array (
        'setter' => 'setDataAbsentReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'interpretation' => 
      array (
        'setter' => 'setInterpretation',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'comments' => 
      array (
        'setter' => 'setComments',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'bodySite' => 
      array (
        'setter' => 'setBodySite',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'method' => 
      array (
        'setter' => 'setMethod',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'specimen' => 
      array (
        'setter' => 'setSpecimen',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'device' => 
      array (
        'setter' => 'setDevice',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'referenceRange' => 
      array (
        'setter' => 'addReferenceRange',
        'element' => 'Observation.ReferenceRange',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRObservation\\FHIRObservationReferenceRange',
      ),
      'related' => 
      array (
        'setter' => 'addRelated',
        'element' => 'Observation.Related',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRObservation\\FHIRObservationRelated',
      ),
      'component' => 
      array (
        'setter' => 'addComponent',
        'element' => 'Observation.Component',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRObservation\\FHIRObservationComponent',
      ),
    ),
  ),
  'Observation.ReferenceRange' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRObservation\\FHIRObservationReferenceRange',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'low' => 
      array (
        'setter' => 'setLow',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'high' => 
      array (
        'setter' => 'setHigh',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'meaning' => 
      array (
        'setter' => 'setMeaning',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'age' => 
      array (
        'setter' => 'setAge',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Observation.Related' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRObservation\\FHIRObservationRelated',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'ObservationRelationshipType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRObservationRelationshipType',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Observation.Component' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRObservation\\FHIRObservationComponent',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'valueQuantity' => 
      array (
        'setter' => 'setValueQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'valueCodeableConcept' => 
      array (
        'setter' => 'setValueCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'valueString' => 
      array (
        'setter' => 'setValueString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valueRange' => 
      array (
        'setter' => 'setValueRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'valueRatio' => 
      array (
        'setter' => 'setValueRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'valueSampledData' => 
      array (
        'setter' => 'setValueSampledData',
        'element' => 'SampledData',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
      ),
      'valueAttachment' => 
      array (
        'setter' => 'setValueAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'valueTime' => 
      array (
        'setter' => 'setValueTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'valueDateTime' => 
      array (
        'setter' => 'setValueDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'valuePeriod' => 
      array (
        'setter' => 'setValuePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'dataAbsentReason' => 
      array (
        'setter' => 'setDataAbsentReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'referenceRange' => 
      array (
        'setter' => 'addReferenceRange',
        'element' => 'Observation.ReferenceRange',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRObservation\\FHIRObservationReferenceRange',
      ),
    ),
  ),
  'ObservationStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRObservationStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ObservationStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'ObservationRelationshipType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRObservationRelationshipType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ObservationRelationshipType-list',
        'type' => 'string',
      ),
    ),
  ),
  'OperationDefinition' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIROperationDefinition',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'kind' => 
      array (
        'setter' => 'setKind',
        'element' => 'OperationKind',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROperationKind',
      ),
      'experimental' => 
      array (
        'setter' => 'setExperimental',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'OperationDefinition.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIROperationDefinition\\FHIROperationDefinitionContact',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'requirements' => 
      array (
        'setter' => 'setRequirements',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'idempotent' => 
      array (
        'setter' => 'setIdempotent',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'notes' => 
      array (
        'setter' => 'setNotes',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'base' => 
      array (
        'setter' => 'setBase',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'instance' => 
      array (
        'setter' => 'setInstance',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'parameter' => 
      array (
        'setter' => 'addParameter',
        'element' => 'OperationDefinition.Parameter',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIROperationDefinition\\FHIROperationDefinitionParameter',
      ),
    ),
  ),
  'OperationDefinition.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIROperationDefinition\\FHIROperationDefinitionContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'OperationDefinition.Parameter' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIROperationDefinition\\FHIROperationDefinitionParameter',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'use' => 
      array (
        'setter' => 'setUse',
        'element' => 'OperationParameterUse',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROperationParameterUse',
      ),
      'min' => 
      array (
        'setter' => 'setMin',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'max' => 
      array (
        'setter' => 'setMax',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'documentation' => 
      array (
        'setter' => 'setDocumentation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'profile' => 
      array (
        'setter' => 'setProfile',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'binding' => 
      array (
        'setter' => 'setBinding',
        'element' => 'OperationDefinition.Binding',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIROperationDefinition\\FHIROperationDefinitionBinding',
      ),
      'part' => 
      array (
        'setter' => 'addPart',
        'element' => 'OperationDefinition.Parameter',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIROperationDefinition\\FHIROperationDefinitionParameter',
      ),
    ),
  ),
  'OperationDefinition.Binding' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIROperationDefinition\\FHIROperationDefinitionBinding',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'strength' => 
      array (
        'setter' => 'setStrength',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'valueSetUri' => 
      array (
        'setter' => 'setValueSetUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'valueSetReference' => 
      array (
        'setter' => 'setValueSetReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'OperationParameterUse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIROperationParameterUse',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'OperationParameterUse-list',
        'type' => 'string',
      ),
    ),
  ),
  'OperationKind' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIROperationKind',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'OperationKind-list',
        'type' => 'string',
      ),
    ),
  ),
  'OperationOutcome' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIROperationOutcome',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'issue' => 
      array (
        'setter' => 'addIssue',
        'element' => 'OperationOutcome.Issue',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIROperationOutcome\\FHIROperationOutcomeIssue',
      ),
    ),
  ),
  'OperationOutcome.Issue' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIROperationOutcome\\FHIROperationOutcomeIssue',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'severity' => 
      array (
        'setter' => 'setSeverity',
        'element' => 'IssueSeverity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIssueSeverity',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'IssueType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIssueType',
      ),
      'details' => 
      array (
        'setter' => 'setDetails',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'diagnostics' => 
      array (
        'setter' => 'setDiagnostics',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'location' => 
      array (
        'setter' => 'addLocation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'IssueType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRIssueType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'IssueType-list',
        'type' => 'string',
      ),
    ),
  ),
  'IssueSeverity' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRIssueSeverity',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'IssueSeverity-list',
        'type' => 'string',
      ),
    ),
  ),
  'Order' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIROrder',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'reasonCodeableConcept' => 
      array (
        'setter' => 'setReasonCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonReference' => 
      array (
        'setter' => 'setReasonReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'when' => 
      array (
        'setter' => 'setWhen',
        'element' => 'Order.When',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIROrder\\FHIROrderWhen',
      ),
      'detail' => 
      array (
        'setter' => 'addDetail',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Order.When' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIROrder\\FHIROrderWhen',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'schedule' => 
      array (
        'setter' => 'setSchedule',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
    ),
  ),
  'OrderResponse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIROrderResponse',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'who' => 
      array (
        'setter' => 'setWho',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'orderStatus' => 
      array (
        'setter' => 'setOrderStatus',
        'element' => 'OrderStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROrderStatus',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'fulfillment' => 
      array (
        'setter' => 'addFulfillment',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'OrderStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIROrderStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'OrderStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Organization' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIROrganization',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'active' => 
      array (
        'setter' => 'setActive',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'address' => 
      array (
        'setter' => 'addAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'partOf' => 
      array (
        'setter' => 'setPartOf',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'Organization.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIROrganization\\FHIROrganizationContact',
      ),
    ),
  ),
  'Organization.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIROrganization\\FHIROrganizationContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'purpose' => 
      array (
        'setter' => 'setPurpose',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'address' => 
      array (
        'setter' => 'setAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
    ),
  ),
  'Parameters' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRParameters',
    'extendedElementName' => 'Resource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'parameter' => 
      array (
        'setter' => 'addParameter',
        'element' => 'Parameters.Parameter',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRParameters\\FHIRParametersParameter',
      ),
    ),
  ),
  'Parameters.Parameter' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRParameters\\FHIRParametersParameter',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valueBoolean' => 
      array (
        'setter' => 'setValueBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'valueInteger' => 
      array (
        'setter' => 'setValueInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'valueDecimal' => 
      array (
        'setter' => 'setValueDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'valueBase64Binary' => 
      array (
        'setter' => 'setValueBase64Binary',
        'element' => 'base64Binary',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBase64Binary',
      ),
      'valueInstant' => 
      array (
        'setter' => 'setValueInstant',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'valueString' => 
      array (
        'setter' => 'setValueString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valueUri' => 
      array (
        'setter' => 'setValueUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'valueDate' => 
      array (
        'setter' => 'setValueDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'valueDateTime' => 
      array (
        'setter' => 'setValueDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'valueTime' => 
      array (
        'setter' => 'setValueTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'valueCode' => 
      array (
        'setter' => 'setValueCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'valueOid' => 
      array (
        'setter' => 'setValueOid',
        'element' => 'oid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIROid',
      ),
      'valueUuid' => 
      array (
        'setter' => 'setValueUuid',
        'element' => 'uuid',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUuid',
      ),
      'valueId' => 
      array (
        'setter' => 'setValueId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'valueUnsignedInt' => 
      array (
        'setter' => 'setValueUnsignedInt',
        'element' => 'unsignedInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUnsignedInt',
      ),
      'valuePositiveInt' => 
      array (
        'setter' => 'setValuePositiveInt',
        'element' => 'positiveInt',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPositiveInt',
      ),
      'valueMarkdown' => 
      array (
        'setter' => 'setValueMarkdown',
        'element' => 'markdown',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMarkdown',
      ),
      'valueAnnotation' => 
      array (
        'setter' => 'setValueAnnotation',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'valueAttachment' => 
      array (
        'setter' => 'setValueAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'valueIdentifier' => 
      array (
        'setter' => 'setValueIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'valueCodeableConcept' => 
      array (
        'setter' => 'setValueCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'valueCoding' => 
      array (
        'setter' => 'setValueCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'valueQuantity' => 
      array (
        'setter' => 'setValueQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'valueRange' => 
      array (
        'setter' => 'setValueRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'valuePeriod' => 
      array (
        'setter' => 'setValuePeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'valueRatio' => 
      array (
        'setter' => 'setValueRatio',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'valueReference' => 
      array (
        'setter' => 'setValueReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'valueSampledData' => 
      array (
        'setter' => 'setValueSampledData',
        'element' => 'SampledData',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSampledData',
      ),
      'valueSignature' => 
      array (
        'setter' => 'setValueSignature',
        'element' => 'Signature',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
      ),
      'valueHumanName' => 
      array (
        'setter' => 'setValueHumanName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'valueAddress' => 
      array (
        'setter' => 'setValueAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'valueContactPoint' => 
      array (
        'setter' => 'setValueContactPoint',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'valueTiming' => 
      array (
        'setter' => 'setValueTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'valueMeta' => 
      array (
        'setter' => 'setValueMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'valueElementDefinition' => 
      array (
        'setter' => 'setValueElementDefinition',
        'element' => 'ElementDefinition',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRElementDefinition',
      ),
      'resource' => 
      array (
        'setter' => 'setResource',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'part' => 
      array (
        'setter' => 'addPart',
        'element' => 'Parameters.Parameter',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRParameters\\FHIRParametersParameter',
      ),
    ),
  ),
  'Patient' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRPatient',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'active' => 
      array (
        'setter' => 'setActive',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'name' => 
      array (
        'setter' => 'addName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'gender' => 
      array (
        'setter' => 'setGender',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'birthDate' => 
      array (
        'setter' => 'setBirthDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'deceasedBoolean' => 
      array (
        'setter' => 'setDeceasedBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'deceasedDateTime' => 
      array (
        'setter' => 'setDeceasedDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'address' => 
      array (
        'setter' => 'addAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'maritalStatus' => 
      array (
        'setter' => 'setMaritalStatus',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'multipleBirthBoolean' => 
      array (
        'setter' => 'setMultipleBirthBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'multipleBirthInteger' => 
      array (
        'setter' => 'setMultipleBirthInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'photo' => 
      array (
        'setter' => 'addPhoto',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'Patient.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRPatient\\FHIRPatientContact',
      ),
      'animal' => 
      array (
        'setter' => 'setAnimal',
        'element' => 'Patient.Animal',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRPatient\\FHIRPatientAnimal',
      ),
      'communication' => 
      array (
        'setter' => 'addCommunication',
        'element' => 'Patient.Communication',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRPatient\\FHIRPatientCommunication',
      ),
      'careProvider' => 
      array (
        'setter' => 'addCareProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'managingOrganization' => 
      array (
        'setter' => 'setManagingOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'link' => 
      array (
        'setter' => 'addLink',
        'element' => 'Patient.Link',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRPatient\\FHIRPatientLink',
      ),
    ),
  ),
  'Patient.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRPatient\\FHIRPatientContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'relationship' => 
      array (
        'setter' => 'addRelationship',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'address' => 
      array (
        'setter' => 'setAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'gender' => 
      array (
        'setter' => 'setGender',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'Patient.Animal' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRPatient\\FHIRPatientAnimal',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'species' => 
      array (
        'setter' => 'setSpecies',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'breed' => 
      array (
        'setter' => 'setBreed',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'genderStatus' => 
      array (
        'setter' => 'setGenderStatus',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'Patient.Communication' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRPatient\\FHIRPatientCommunication',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'preferred' => 
      array (
        'setter' => 'setPreferred',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
    ),
  ),
  'Patient.Link' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRPatient\\FHIRPatientLink',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'other' => 
      array (
        'setter' => 'setOther',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'LinkType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRLinkType',
      ),
    ),
  ),
  'LinkType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRLinkType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'LinkType-list',
        'type' => 'string',
      ),
    ),
  ),
  'PaymentNotice' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRPaymentNotice',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'provider' => 
      array (
        'setter' => 'setProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'response' => 
      array (
        'setter' => 'setResponse',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'paymentStatus' => 
      array (
        'setter' => 'setPaymentStatus',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'PaymentReconciliation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRPaymentReconciliation',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'outcome' => 
      array (
        'setter' => 'setOutcome',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'disposition' => 
      array (
        'setter' => 'setDisposition',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestProvider' => 
      array (
        'setter' => 'setRequestProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestOrganization' => 
      array (
        'setter' => 'setRequestOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'detail' => 
      array (
        'setter' => 'addDetail',
        'element' => 'PaymentReconciliation.Detail',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRPaymentReconciliation\\FHIRPaymentReconciliationDetail',
      ),
      'form' => 
      array (
        'setter' => 'setForm',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'total' => 
      array (
        'setter' => 'setTotal',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
      'note' => 
      array (
        'setter' => 'addNote',
        'element' => 'PaymentReconciliation.Note',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRPaymentReconciliation\\FHIRPaymentReconciliationNote',
      ),
    ),
  ),
  'PaymentReconciliation.Detail' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRPaymentReconciliation\\FHIRPaymentReconciliationDetail',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'responce' => 
      array (
        'setter' => 'setResponce',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'submitter' => 
      array (
        'setter' => 'setSubmitter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'payee' => 
      array (
        'setter' => 'setPayee',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'amount' => 
      array (
        'setter' => 'setAmount',
        'element' => 'Money',
        'type' => 'FHIR_DSTU_TWO\\FHIRMoney',
      ),
    ),
  ),
  'PaymentReconciliation.Note' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRPaymentReconciliation\\FHIRPaymentReconciliationNote',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Person' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRPerson',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'name' => 
      array (
        'setter' => 'addName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'gender' => 
      array (
        'setter' => 'setGender',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'birthDate' => 
      array (
        'setter' => 'setBirthDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'address' => 
      array (
        'setter' => 'addAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'photo' => 
      array (
        'setter' => 'setPhoto',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'managingOrganization' => 
      array (
        'setter' => 'setManagingOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'active' => 
      array (
        'setter' => 'setActive',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'link' => 
      array (
        'setter' => 'addLink',
        'element' => 'Person.Link',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRPerson\\FHIRPersonLink',
      ),
    ),
  ),
  'Person.Link' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRPerson\\FHIRPersonLink',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'assurance' => 
      array (
        'setter' => 'setAssurance',
        'element' => 'IdentityAssuranceLevel',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentityAssuranceLevel',
      ),
    ),
  ),
  'IdentityAssuranceLevel' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentityAssuranceLevel',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'IdentityAssuranceLevel-list',
        'type' => 'string',
      ),
    ),
  ),
  'Practitioner' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRPractitioner',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'active' => 
      array (
        'setter' => 'setActive',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'address' => 
      array (
        'setter' => 'addAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'gender' => 
      array (
        'setter' => 'setGender',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'birthDate' => 
      array (
        'setter' => 'setBirthDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'photo' => 
      array (
        'setter' => 'addPhoto',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'practitionerRole' => 
      array (
        'setter' => 'addPractitionerRole',
        'element' => 'Practitioner.PractitionerRole',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRPractitioner\\FHIRPractitionerPractitionerRole',
      ),
      'qualification' => 
      array (
        'setter' => 'addQualification',
        'element' => 'Practitioner.Qualification',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRPractitioner\\FHIRPractitionerQualification',
      ),
      'communication' => 
      array (
        'setter' => 'addCommunication',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'Practitioner.PractitionerRole' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRPractitioner\\FHIRPractitionerPractitionerRole',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'managingOrganization' => 
      array (
        'setter' => 'setManagingOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'role' => 
      array (
        'setter' => 'setRole',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'specialty' => 
      array (
        'setter' => 'addSpecialty',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'location' => 
      array (
        'setter' => 'addLocation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'healthcareService' => 
      array (
        'setter' => 'addHealthcareService',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Practitioner.Qualification' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRPractitioner\\FHIRPractitionerQualification',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'issuer' => 
      array (
        'setter' => 'setIssuer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Procedure' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRProcedure',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'ProcedureStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRProcedureStatus',
      ),
      'category' => 
      array (
        'setter' => 'setCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'notPerformed' => 
      array (
        'setter' => 'setNotPerformed',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'reasonNotPerformed' => 
      array (
        'setter' => 'addReasonNotPerformed',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'bodySite' => 
      array (
        'setter' => 'addBodySite',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonCodeableConcept' => 
      array (
        'setter' => 'setReasonCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonReference' => 
      array (
        'setter' => 'setReasonReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'performer' => 
      array (
        'setter' => 'addPerformer',
        'element' => 'Procedure.Performer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRProcedure\\FHIRProcedurePerformer',
      ),
      'performedDateTime' => 
      array (
        'setter' => 'setPerformedDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'performedPeriod' => 
      array (
        'setter' => 'setPerformedPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'location' => 
      array (
        'setter' => 'setLocation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'outcome' => 
      array (
        'setter' => 'setOutcome',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'report' => 
      array (
        'setter' => 'addReport',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'complication' => 
      array (
        'setter' => 'addComplication',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'followUp' => 
      array (
        'setter' => 'addFollowUp',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'notes' => 
      array (
        'setter' => 'addNotes',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'focalDevice' => 
      array (
        'setter' => 'addFocalDevice',
        'element' => 'Procedure.FocalDevice',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRProcedure\\FHIRProcedureFocalDevice',
      ),
      'used' => 
      array (
        'setter' => 'addUsed',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Procedure.Performer' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRProcedure\\FHIRProcedurePerformer',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'actor' => 
      array (
        'setter' => 'setActor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'role' => 
      array (
        'setter' => 'setRole',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'Procedure.FocalDevice' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRProcedure\\FHIRProcedureFocalDevice',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'action' => 
      array (
        'setter' => 'setAction',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'manipulated' => 
      array (
        'setter' => 'setManipulated',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'ProcedureStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRProcedureStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ProcedureStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'ProcedureRequest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRProcedureRequest',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'bodySite' => 
      array (
        'setter' => 'addBodySite',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonCodeableConcept' => 
      array (
        'setter' => 'setReasonCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonReference' => 
      array (
        'setter' => 'setReasonReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'scheduledDateTime' => 
      array (
        'setter' => 'setScheduledDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'scheduledPeriod' => 
      array (
        'setter' => 'setScheduledPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'scheduledTiming' => 
      array (
        'setter' => 'setScheduledTiming',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'performer' => 
      array (
        'setter' => 'setPerformer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'ProcedureRequestStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRProcedureRequestStatus',
      ),
      'notes' => 
      array (
        'setter' => 'addNotes',
        'element' => 'Annotation',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnnotation',
      ),
      'asNeededBoolean' => 
      array (
        'setter' => 'setAsNeededBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'asNeededCodeableConcept' => 
      array (
        'setter' => 'setAsNeededCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'orderedOn' => 
      array (
        'setter' => 'setOrderedOn',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'orderer' => 
      array (
        'setter' => 'setOrderer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'priority' => 
      array (
        'setter' => 'setPriority',
        'element' => 'ProcedureRequestPriority',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRProcedureRequestPriority',
      ),
    ),
  ),
  'ProcedureRequestPriority' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRProcedureRequestPriority',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ProcedureRequestPriority-list',
        'type' => 'string',
      ),
    ),
  ),
  'ProcedureRequestStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRProcedureRequestStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ProcedureRequestStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'ProcessRequest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRProcessRequest',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'action' => 
      array (
        'setter' => 'setAction',
        'element' => 'ActionList',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRActionList',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'provider' => 
      array (
        'setter' => 'setProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'response' => 
      array (
        'setter' => 'setResponse',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'nullify' => 
      array (
        'setter' => 'setNullify',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'reference' => 
      array (
        'setter' => 'setReference',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'item' => 
      array (
        'setter' => 'addItem',
        'element' => 'ProcessRequest.Item',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRProcessRequest\\FHIRProcessRequestItem',
      ),
      'include' => 
      array (
        'setter' => 'addInclude',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'exclude' => 
      array (
        'setter' => 'addExclude',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'ProcessRequest.Item' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRProcessRequest\\FHIRProcessRequestItem',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'sequenceLinkId' => 
      array (
        'setter' => 'setSequenceLinkId',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
    ),
  ),
  'ActionList' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRActionList',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ActionList-list',
        'type' => 'string',
      ),
    ),
  ),
  'ProcessResponse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRProcessResponse',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'request' => 
      array (
        'setter' => 'setRequest',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'outcome' => 
      array (
        'setter' => 'setOutcome',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'disposition' => 
      array (
        'setter' => 'setDisposition',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'ruleset' => 
      array (
        'setter' => 'setRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'originalRuleset' => 
      array (
        'setter' => 'setOriginalRuleset',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'created' => 
      array (
        'setter' => 'setCreated',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'organization' => 
      array (
        'setter' => 'setOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestProvider' => 
      array (
        'setter' => 'setRequestProvider',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requestOrganization' => 
      array (
        'setter' => 'setRequestOrganization',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'form' => 
      array (
        'setter' => 'setForm',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'notes' => 
      array (
        'setter' => 'addNotes',
        'element' => 'ProcessResponse.Notes',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRProcessResponse\\FHIRProcessResponseNotes',
      ),
      'error' => 
      array (
        'setter' => 'addError',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'ProcessResponse.Notes' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRProcessResponse\\FHIRProcessResponseNotes',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Provenance' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRProvenance',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'target' => 
      array (
        'setter' => 'addTarget',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'recorded' => 
      array (
        'setter' => 'setRecorded',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'reason' => 
      array (
        'setter' => 'addReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'activity' => 
      array (
        'setter' => 'setActivity',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'location' => 
      array (
        'setter' => 'setLocation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'policy' => 
      array (
        'setter' => 'addPolicy',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'agent' => 
      array (
        'setter' => 'addAgent',
        'element' => 'Provenance.Agent',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRProvenance\\FHIRProvenanceAgent',
      ),
      'entity' => 
      array (
        'setter' => 'addEntity',
        'element' => 'Provenance.Entity',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRProvenance\\FHIRProvenanceEntity',
      ),
      'signature' => 
      array (
        'setter' => 'addSignature',
        'element' => 'Signature',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSignature',
      ),
    ),
  ),
  'Provenance.Agent' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRProvenance\\FHIRProvenanceAgent',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'role' => 
      array (
        'setter' => 'setRole',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'actor' => 
      array (
        'setter' => 'setActor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'userId' => 
      array (
        'setter' => 'setUserId',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'relatedAgent' => 
      array (
        'setter' => 'addRelatedAgent',
        'element' => 'Provenance.RelatedAgent',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRProvenance\\FHIRProvenanceRelatedAgent',
      ),
    ),
  ),
  'Provenance.RelatedAgent' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRProvenance\\FHIRProvenanceRelatedAgent',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'target' => 
      array (
        'setter' => 'setTarget',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
    ),
  ),
  'Provenance.Entity' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRProvenance\\FHIRProvenanceEntity',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'role' => 
      array (
        'setter' => 'setRole',
        'element' => 'ProvenanceEntityRole',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRProvenanceEntityRole',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'reference' => 
      array (
        'setter' => 'setReference',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'display' => 
      array (
        'setter' => 'setDisplay',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'agent' => 
      array (
        'setter' => 'setAgent',
        'element' => 'Provenance.Agent',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRProvenance\\FHIRProvenanceAgent',
      ),
    ),
  ),
  'ProvenanceEntityRole' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRProvenanceEntityRole',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ProvenanceEntityRole-list',
        'type' => 'string',
      ),
    ),
  ),
  'Questionnaire' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRQuestionnaire',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'QuestionnaireStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuestionnaireStatus',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'subjectType' => 
      array (
        'setter' => 'addSubjectType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'group' => 
      array (
        'setter' => 'setGroup',
        'element' => 'Questionnaire.Group',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaire\\FHIRQuestionnaireGroup',
      ),
    ),
  ),
  'Questionnaire.Group' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaire\\FHIRQuestionnaireGroup',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'linkId' => 
      array (
        'setter' => 'setLinkId',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'title' => 
      array (
        'setter' => 'setTitle',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'concept' => 
      array (
        'setter' => 'addConcept',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'required' => 
      array (
        'setter' => 'setRequired',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'repeats' => 
      array (
        'setter' => 'setRepeats',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'group' => 
      array (
        'setter' => 'addGroup',
        'element' => 'Questionnaire.Group',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaire\\FHIRQuestionnaireGroup',
      ),
      'question' => 
      array (
        'setter' => 'addQuestion',
        'element' => 'Questionnaire.Question',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaire\\FHIRQuestionnaireQuestion',
      ),
    ),
  ),
  'Questionnaire.Question' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaire\\FHIRQuestionnaireQuestion',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'linkId' => 
      array (
        'setter' => 'setLinkId',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'concept' => 
      array (
        'setter' => 'addConcept',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'AnswerFormat',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAnswerFormat',
      ),
      'required' => 
      array (
        'setter' => 'setRequired',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'repeats' => 
      array (
        'setter' => 'setRepeats',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'options' => 
      array (
        'setter' => 'setOptions',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'option' => 
      array (
        'setter' => 'addOption',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'group' => 
      array (
        'setter' => 'addGroup',
        'element' => 'Questionnaire.Group',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaire\\FHIRQuestionnaireGroup',
      ),
    ),
  ),
  'QuestionnaireStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRQuestionnaireStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'QuestionnaireStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'AnswerFormat' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAnswerFormat',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AnswerFormat-list',
        'type' => 'string',
      ),
    ),
  ),
  'QuestionnaireAnswers' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRQuestionnaireAnswers',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'questionnaire' => 
      array (
        'setter' => 'setQuestionnaire',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'QuestionnaireAnswersStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuestionnaireAnswersStatus',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'authored' => 
      array (
        'setter' => 'setAuthored',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'group' => 
      array (
        'setter' => 'setGroup',
        'element' => 'QuestionnaireAnswers.Group',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireAnswers\\FHIRQuestionnaireAnswersGroup',
      ),
    ),
  ),
  'QuestionnaireAnswers.Group' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireAnswers\\FHIRQuestionnaireAnswersGroup',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'linkId' => 
      array (
        'setter' => 'setLinkId',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'title' => 
      array (
        'setter' => 'setTitle',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'group' => 
      array (
        'setter' => 'addGroup',
        'element' => 'QuestionnaireAnswers.Group',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireAnswers\\FHIRQuestionnaireAnswersGroup',
      ),
      'question' => 
      array (
        'setter' => 'addQuestion',
        'element' => 'QuestionnaireAnswers.Question',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireAnswers\\FHIRQuestionnaireAnswersQuestion',
      ),
    ),
  ),
  'QuestionnaireAnswers.Question' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireAnswers\\FHIRQuestionnaireAnswersQuestion',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'linkId' => 
      array (
        'setter' => 'setLinkId',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'answer' => 
      array (
        'setter' => 'addAnswer',
        'element' => 'QuestionnaireAnswers.Answer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireAnswers\\FHIRQuestionnaireAnswersAnswer',
      ),
    ),
  ),
  'QuestionnaireAnswers.Answer' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireAnswers\\FHIRQuestionnaireAnswersAnswer',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'valueBoolean' => 
      array (
        'setter' => 'setValueBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'valueDecimal' => 
      array (
        'setter' => 'setValueDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'valueInteger' => 
      array (
        'setter' => 'setValueInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'valueDate' => 
      array (
        'setter' => 'setValueDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'valueDateTime' => 
      array (
        'setter' => 'setValueDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'valueInstant' => 
      array (
        'setter' => 'setValueInstant',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'valueTime' => 
      array (
        'setter' => 'setValueTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'valueString' => 
      array (
        'setter' => 'setValueString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valueUri' => 
      array (
        'setter' => 'setValueUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'valueAttachment' => 
      array (
        'setter' => 'setValueAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'valueCoding' => 
      array (
        'setter' => 'setValueCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'valueMeasuredQuantity' => 
      array (
        'setter' => 'setValueMeasuredQuantity',
        'element' => 'MeasuredQuantity',
        'type' => NULL,
      ),
      'valueReference' => 
      array (
        'setter' => 'setValueReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'group' => 
      array (
        'setter' => 'addGroup',
        'element' => 'QuestionnaireAnswers.Group',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireAnswers\\FHIRQuestionnaireAnswersGroup',
      ),
    ),
  ),
  'QuestionnaireAnswersStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRQuestionnaireAnswersStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'QuestionnaireAnswersStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'QuestionnaireResponse' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRQuestionnaireResponse',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'questionnaire' => 
      array (
        'setter' => 'setQuestionnaire',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'QuestionnaireResponseStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuestionnaireResponseStatus',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'author' => 
      array (
        'setter' => 'setAuthor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'authored' => 
      array (
        'setter' => 'setAuthored',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'group' => 
      array (
        'setter' => 'setGroup',
        'element' => 'QuestionnaireResponse.Group',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireResponse\\FHIRQuestionnaireResponseGroup',
      ),
    ),
  ),
  'QuestionnaireResponse.Group' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireResponse\\FHIRQuestionnaireResponseGroup',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'linkId' => 
      array (
        'setter' => 'setLinkId',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'title' => 
      array (
        'setter' => 'setTitle',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'group' => 
      array (
        'setter' => 'addGroup',
        'element' => 'QuestionnaireResponse.Group',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireResponse\\FHIRQuestionnaireResponseGroup',
      ),
      'question' => 
      array (
        'setter' => 'addQuestion',
        'element' => 'QuestionnaireResponse.Question',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireResponse\\FHIRQuestionnaireResponseQuestion',
      ),
    ),
  ),
  'QuestionnaireResponse.Question' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireResponse\\FHIRQuestionnaireResponseQuestion',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'linkId' => 
      array (
        'setter' => 'setLinkId',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'answer' => 
      array (
        'setter' => 'addAnswer',
        'element' => 'QuestionnaireResponse.Answer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireResponse\\FHIRQuestionnaireResponseAnswer',
      ),
    ),
  ),
  'QuestionnaireResponse.Answer' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireResponse\\FHIRQuestionnaireResponseAnswer',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'valueBoolean' => 
      array (
        'setter' => 'setValueBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'valueDecimal' => 
      array (
        'setter' => 'setValueDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'valueInteger' => 
      array (
        'setter' => 'setValueInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'valueDate' => 
      array (
        'setter' => 'setValueDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'valueDateTime' => 
      array (
        'setter' => 'setValueDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'valueInstant' => 
      array (
        'setter' => 'setValueInstant',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'valueTime' => 
      array (
        'setter' => 'setValueTime',
        'element' => 'time',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTime',
      ),
      'valueString' => 
      array (
        'setter' => 'setValueString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valueUri' => 
      array (
        'setter' => 'setValueUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'valueAttachment' => 
      array (
        'setter' => 'setValueAttachment',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'valueCoding' => 
      array (
        'setter' => 'setValueCoding',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'valueQuantity' => 
      array (
        'setter' => 'setValueQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'valueReference' => 
      array (
        'setter' => 'setValueReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'group' => 
      array (
        'setter' => 'addGroup',
        'element' => 'QuestionnaireResponse.Group',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRQuestionnaireResponse\\FHIRQuestionnaireResponseGroup',
      ),
    ),
  ),
  'QuestionnaireResponseStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRQuestionnaireResponseStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'QuestionnaireResponseStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'ReferralRequest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRReferralRequest',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'ReferralStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReferralStatus',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'specialty' => 
      array (
        'setter' => 'setSpecialty',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'priority' => 
      array (
        'setter' => 'setPriority',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'requester' => 
      array (
        'setter' => 'setRequester',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'recipient' => 
      array (
        'setter' => 'addRecipient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dateSent' => 
      array (
        'setter' => 'setDateSent',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'reason' => 
      array (
        'setter' => 'setReason',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'serviceRequested' => 
      array (
        'setter' => 'addServiceRequested',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'supportingInformation' => 
      array (
        'setter' => 'addSupportingInformation',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'fulfillmentTime' => 
      array (
        'setter' => 'setFulfillmentTime',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'ReferralStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRReferralStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ReferralStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'RelatedPerson' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRRelatedPerson',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'relationship' => 
      array (
        'setter' => 'setRelationship',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'HumanName',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRHumanName',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'gender' => 
      array (
        'setter' => 'setGender',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'birthDate' => 
      array (
        'setter' => 'setBirthDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'address' => 
      array (
        'setter' => 'addAddress',
        'element' => 'Address',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAddress',
      ),
      'photo' => 
      array (
        'setter' => 'addPhoto',
        'element' => 'Attachment',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAttachment',
      ),
      'period' => 
      array (
        'setter' => 'setPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
    ),
  ),
  'RiskAssessment' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRRiskAssessment',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'condition' => 
      array (
        'setter' => 'setCondition',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'performer' => 
      array (
        'setter' => 'setPerformer',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'method' => 
      array (
        'setter' => 'setMethod',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'basis' => 
      array (
        'setter' => 'addBasis',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'prediction' => 
      array (
        'setter' => 'addPrediction',
        'element' => 'RiskAssessment.Prediction',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRRiskAssessment\\FHIRRiskAssessmentPrediction',
      ),
      'mitigation' => 
      array (
        'setter' => 'setMitigation',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'RiskAssessment.Prediction' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRRiskAssessment\\FHIRRiskAssessmentPrediction',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'outcome' => 
      array (
        'setter' => 'setOutcome',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'probabilityDecimal' => 
      array (
        'setter' => 'setProbabilityDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'probabilityRange' => 
      array (
        'setter' => 'setProbabilityRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'probabilityCodeableConcept' => 
      array (
        'setter' => 'setProbabilityCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'relativeRisk' => 
      array (
        'setter' => 'setRelativeRisk',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'whenPeriod' => 
      array (
        'setter' => 'setWhenPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'whenRange' => 
      array (
        'setter' => 'setWhenRange',
        'element' => 'Range',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRange',
      ),
      'rationale' => 
      array (
        'setter' => 'setRationale',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'Schedule' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSchedule',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'type' => 
      array (
        'setter' => 'addType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'actor' => 
      array (
        'setter' => 'setActor',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'planningHorizon' => 
      array (
        'setter' => 'setPlanningHorizon',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'comment' => 
      array (
        'setter' => 'setComment',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'SearchParameter' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSearchParameter',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'experimental' => 
      array (
        'setter' => 'setExperimental',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'SearchParameter.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRSearchParameter\\FHIRSearchParameterContact',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'requirements' => 
      array (
        'setter' => 'setRequirements',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'base' => 
      array (
        'setter' => 'setBase',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'xpath' => 
      array (
        'setter' => 'setXpath',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'xpathUsage' => 
      array (
        'setter' => 'setXpathUsage',
        'element' => 'XPathUsageType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRXPathUsageType',
      ),
      'target' => 
      array (
        'setter' => 'addTarget',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'SearchParameter.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRSearchParameter\\FHIRSearchParameterContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'XPathUsageType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRXPathUsageType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'XPathUsageType-list',
        'type' => 'string',
      ),
    ),
  ),
  'Slot' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSlot',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'schedule' => 
      array (
        'setter' => 'setSchedule',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'freeBusyType' => 
      array (
        'setter' => 'setFreeBusyType',
        'element' => 'SlotStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSlotStatus',
      ),
      'start' => 
      array (
        'setter' => 'setStart',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'end' => 
      array (
        'setter' => 'setEnd',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'overbooked' => 
      array (
        'setter' => 'setOverbooked',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'comment' => 
      array (
        'setter' => 'setComment',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'SlotStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSlotStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SlotStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'Specimen' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSpecimen',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'SpecimenStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSpecimenStatus',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'parent' => 
      array (
        'setter' => 'addParent',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'subject' => 
      array (
        'setter' => 'setSubject',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'accessionIdentifier' => 
      array (
        'setter' => 'setAccessionIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'receivedTime' => 
      array (
        'setter' => 'setReceivedTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'collection' => 
      array (
        'setter' => 'setCollection',
        'element' => 'Specimen.Collection',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRSpecimen\\FHIRSpecimenCollection',
      ),
      'treatment' => 
      array (
        'setter' => 'addTreatment',
        'element' => 'Specimen.Treatment',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRSpecimen\\FHIRSpecimenTreatment',
      ),
      'container' => 
      array (
        'setter' => 'addContainer',
        'element' => 'Specimen.Container',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRSpecimen\\FHIRSpecimenContainer',
      ),
    ),
  ),
  'Specimen.Collection' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRSpecimen\\FHIRSpecimenCollection',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'collector' => 
      array (
        'setter' => 'setCollector',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'comment' => 
      array (
        'setter' => 'addComment',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'collectedDateTime' => 
      array (
        'setter' => 'setCollectedDateTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'collectedPeriod' => 
      array (
        'setter' => 'setCollectedPeriod',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'method' => 
      array (
        'setter' => 'setMethod',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'bodySite' => 
      array (
        'setter' => 'setBodySite',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
    ),
  ),
  'Specimen.Treatment' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRSpecimen\\FHIRSpecimenTreatment',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'procedure' => 
      array (
        'setter' => 'setProcedure',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'additive' => 
      array (
        'setter' => 'addAdditive',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Specimen.Container' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRSpecimen\\FHIRSpecimenContainer',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'capacity' => 
      array (
        'setter' => 'setCapacity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'specimenQuantity' => 
      array (
        'setter' => 'setSpecimenQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'additiveCodeableConcept' => 
      array (
        'setter' => 'setAdditiveCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'additiveReference' => 
      array (
        'setter' => 'setAdditiveReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'SpecimenStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSpecimenStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SpecimenStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'StructureDefinition' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRStructureDefinition',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'display' => 
      array (
        'setter' => 'setDisplay',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'experimental' => 
      array (
        'setter' => 'setExperimental',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'StructureDefinition.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRStructureDefinition\\FHIRStructureDefinitionContact',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'useContext' => 
      array (
        'setter' => 'addUseContext',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'requirements' => 
      array (
        'setter' => 'setRequirements',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'copyright' => 
      array (
        'setter' => 'setCopyright',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'code' => 
      array (
        'setter' => 'addCode',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'fhirVersion' => 
      array (
        'setter' => 'setFhirVersion',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'mapping' => 
      array (
        'setter' => 'addMapping',
        'element' => 'StructureDefinition.Mapping',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRStructureDefinition\\FHIRStructureDefinitionMapping',
      ),
      'kind' => 
      array (
        'setter' => 'setKind',
        'element' => 'StructureDefinitionKind',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRStructureDefinitionKind',
      ),
      'constrainedType' => 
      array (
        'setter' => 'setConstrainedType',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'abstract' => 
      array (
        'setter' => 'setAbstract',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'contextType' => 
      array (
        'setter' => 'setContextType',
        'element' => 'ExtensionContext',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtensionContext',
      ),
      'context' => 
      array (
        'setter' => 'addContext',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'base' => 
      array (
        'setter' => 'setBase',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'snapshot' => 
      array (
        'setter' => 'setSnapshot',
        'element' => 'StructureDefinition.Snapshot',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRStructureDefinition\\FHIRStructureDefinitionSnapshot',
      ),
      'differential' => 
      array (
        'setter' => 'setDifferential',
        'element' => 'StructureDefinition.Differential',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRStructureDefinition\\FHIRStructureDefinitionDifferential',
      ),
    ),
  ),
  'StructureDefinition.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRStructureDefinition\\FHIRStructureDefinitionContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'StructureDefinition.Mapping' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRStructureDefinition\\FHIRStructureDefinitionMapping',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identity' => 
      array (
        'setter' => 'setIdentity',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'uri' => 
      array (
        'setter' => 'setUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'comments' => 
      array (
        'setter' => 'setComments',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'StructureDefinition.Snapshot' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRStructureDefinition\\FHIRStructureDefinitionSnapshot',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'element' => 
      array (
        'setter' => 'addElement',
        'element' => 'ElementDefinition',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRElementDefinition',
      ),
    ),
  ),
  'StructureDefinition.Differential' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRStructureDefinition\\FHIRStructureDefinitionDifferential',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'element' => 
      array (
        'setter' => 'addElement',
        'element' => 'ElementDefinition',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRElementDefinition',
      ),
    ),
  ),
  'StructureDefinitionKind' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRStructureDefinitionKind',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'StructureDefinitionKind-list',
        'type' => 'string',
      ),
    ),
  ),
  'ExtensionContext' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRExtensionContext',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ExtensionContext-list',
        'type' => 'string',
      ),
    ),
  ),
  'Subscription' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSubscription',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'criteria' => 
      array (
        'setter' => 'setCriteria',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
      'reason' => 
      array (
        'setter' => 'setReason',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'SubscriptionStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSubscriptionStatus',
      ),
      'error' => 
      array (
        'setter' => 'setError',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'channel' => 
      array (
        'setter' => 'setChannel',
        'element' => 'Subscription.Channel',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRSubscription\\FHIRSubscriptionChannel',
      ),
      'end' => 
      array (
        'setter' => 'setEnd',
        'element' => 'instant',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInstant',
      ),
      'tag' => 
      array (
        'setter' => 'addTag',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
    ),
  ),
  'Subscription.Channel' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRSubscription\\FHIRSubscriptionChannel',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'SubscriptionChannelType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSubscriptionChannelType',
      ),
      'endpoint' => 
      array (
        'setter' => 'setEndpoint',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'payload' => 
      array (
        'setter' => 'setPayload',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'header' => 
      array (
        'setter' => 'setHeader',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'SubscriptionStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSubscriptionStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SubscriptionStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'SubscriptionChannelType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSubscriptionChannelType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SubscriptionChannelType-list',
        'type' => 'string',
      ),
    ),
  ),
  'Substance' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSubstance',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'category' => 
      array (
        'setter' => 'addCategory',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'instance' => 
      array (
        'setter' => 'addInstance',
        'element' => 'Substance.Instance',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRSubstance\\FHIRSubstanceInstance',
      ),
      'ingredient' => 
      array (
        'setter' => 'addIngredient',
        'element' => 'Substance.Ingredient',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRSubstance\\FHIRSubstanceIngredient',
      ),
    ),
  ),
  'Substance.Instance' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRSubstance\\FHIRSubstanceInstance',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'expiry' => 
      array (
        'setter' => 'setExpiry',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
    ),
  ),
  'Substance.Ingredient' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRSubstance\\FHIRSubstanceIngredient',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'Ratio',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRRatio',
      ),
      'substance' => 
      array (
        'setter' => 'setSubstance',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'Supply' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSupply',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'kind' => 
      array (
        'setter' => 'setKind',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'SupplyStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSupplyStatus',
      ),
      'orderedItem' => 
      array (
        'setter' => 'setOrderedItem',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dispense' => 
      array (
        'setter' => 'addDispense',
        'element' => 'Supply.Dispense',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRSupply\\FHIRSupplyDispense',
      ),
    ),
  ),
  'Supply.Dispense' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRSupply\\FHIRSupplyDispense',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'SupplyDispenseStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSupplyDispenseStatus',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'Quantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRQuantity',
      ),
      'suppliedItem' => 
      array (
        'setter' => 'setSuppliedItem',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'supplier' => 
      array (
        'setter' => 'setSupplier',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'whenPrepared' => 
      array (
        'setter' => 'setWhenPrepared',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'whenHandedOver' => 
      array (
        'setter' => 'setWhenHandedOver',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'destination' => 
      array (
        'setter' => 'setDestination',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'receiver' => 
      array (
        'setter' => 'addReceiver',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'SupplyDispenseStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSupplyDispenseStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SupplyDispenseStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'SupplyStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSupplyStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SupplyStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'SupplyDelivery' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSupplyDelivery',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'SupplyDeliveryStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSupplyDeliveryStatus',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'quantity' => 
      array (
        'setter' => 'setQuantity',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'suppliedItem' => 
      array (
        'setter' => 'setSuppliedItem',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'supplier' => 
      array (
        'setter' => 'setSupplier',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'whenPrepared' => 
      array (
        'setter' => 'setWhenPrepared',
        'element' => 'Period',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRPeriod',
      ),
      'time' => 
      array (
        'setter' => 'setTime',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'destination' => 
      array (
        'setter' => 'setDestination',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'receiver' => 
      array (
        'setter' => 'addReceiver',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'SupplyDeliveryStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSupplyDeliveryStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SupplyDeliveryStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'SupplyRequest' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRSupplyRequest',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'source' => 
      array (
        'setter' => 'setSource',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'SupplyRequestStatus',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRSupplyRequestStatus',
      ),
      'kind' => 
      array (
        'setter' => 'setKind',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'orderedItem' => 
      array (
        'setter' => 'setOrderedItem',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'supplier' => 
      array (
        'setter' => 'addSupplier',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'reasonCodeableConcept' => 
      array (
        'setter' => 'setReasonCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonReference' => 
      array (
        'setter' => 'setReasonReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'when' => 
      array (
        'setter' => 'setWhen',
        'element' => 'SupplyRequest.When',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRSupplyRequest\\FHIRSupplyRequestWhen',
      ),
    ),
  ),
  'SupplyRequest.When' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRSupplyRequest\\FHIRSupplyRequestWhen',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'schedule' => 
      array (
        'setter' => 'setSchedule',
        'element' => 'Timing',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRTiming',
      ),
    ),
  ),
  'SupplyRequestStatus' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRSupplyRequestStatus',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'SupplyRequestStatus-list',
        'type' => 'string',
      ),
    ),
  ),
  'TestScript' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRTestScript',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'experimental' => 
      array (
        'setter' => 'setExperimental',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'TestScript.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptContact',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'useContext' => 
      array (
        'setter' => 'addUseContext',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'requirements' => 
      array (
        'setter' => 'setRequirements',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'copyright' => 
      array (
        'setter' => 'setCopyright',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'metadata' => 
      array (
        'setter' => 'setMetadata',
        'element' => 'TestScript.Metadata',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptMetadata',
      ),
      'multiserver' => 
      array (
        'setter' => 'setMultiserver',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'fixture' => 
      array (
        'setter' => 'addFixture',
        'element' => 'TestScript.Fixture',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptFixture',
      ),
      'profile' => 
      array (
        'setter' => 'addProfile',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'variable' => 
      array (
        'setter' => 'addVariable',
        'element' => 'TestScript.Variable',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptVariable',
      ),
      'setup' => 
      array (
        'setter' => 'setSetup',
        'element' => 'TestScript.Setup',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptSetup',
      ),
      'test' => 
      array (
        'setter' => 'addTest',
        'element' => 'TestScript.Test',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptTest',
      ),
      'teardown' => 
      array (
        'setter' => 'setTeardown',
        'element' => 'TestScript.Teardown',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptTeardown',
      ),
    ),
  ),
  'TestScript.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'TestScript.Metadata' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptMetadata',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'link' => 
      array (
        'setter' => 'addLink',
        'element' => 'TestScript.Link',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptLink',
      ),
      'capability' => 
      array (
        'setter' => 'addCapability',
        'element' => 'TestScript.Capability',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptCapability',
      ),
    ),
  ),
  'TestScript.Link' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptLink',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'TestScript.Capability' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptCapability',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'required' => 
      array (
        'setter' => 'setRequired',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'validated' => 
      array (
        'setter' => 'setValidated',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'destination' => 
      array (
        'setter' => 'setDestination',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'link' => 
      array (
        'setter' => 'addLink',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'conformance' => 
      array (
        'setter' => 'setConformance',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'TestScript.Fixture' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptFixture',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'autocreate' => 
      array (
        'setter' => 'setAutocreate',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'autodelete' => 
      array (
        'setter' => 'setAutodelete',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'resource' => 
      array (
        'setter' => 'setResource',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
    ),
  ),
  'TestScript.Variable' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptVariable',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'headerField' => 
      array (
        'setter' => 'setHeaderField',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'path' => 
      array (
        'setter' => 'setPath',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'sourceId' => 
      array (
        'setter' => 'setSourceId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
    ),
  ),
  'TestScript.Setup' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptSetup',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'metadata' => 
      array (
        'setter' => 'setMetadata',
        'element' => 'TestScript.Metadata',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptMetadata',
      ),
      'action' => 
      array (
        'setter' => 'addAction',
        'element' => 'TestScript.Action',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptAction',
      ),
    ),
  ),
  'TestScript.Action' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptAction',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'operation' => 
      array (
        'setter' => 'setOperation',
        'element' => 'TestScript.Operation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptOperation',
      ),
      'assert' => 
      array (
        'setter' => 'setAssert',
        'element' => 'TestScript.Assert',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptAssert',
      ),
    ),
  ),
  'TestScript.Operation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptOperation',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'type' => 
      array (
        'setter' => 'setType',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'resource' => 
      array (
        'setter' => 'setResource',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'label' => 
      array (
        'setter' => 'setLabel',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'accept' => 
      array (
        'setter' => 'setAccept',
        'element' => 'ContentType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContentType',
      ),
      'contentType' => 
      array (
        'setter' => 'setContentType',
        'element' => 'ContentType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContentType',
      ),
      'destination' => 
      array (
        'setter' => 'setDestination',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'encodeRequestUrl' => 
      array (
        'setter' => 'setEncodeRequestUrl',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'params' => 
      array (
        'setter' => 'setParams',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'requestHeader' => 
      array (
        'setter' => 'addRequestHeader',
        'element' => 'TestScript.RequestHeader',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptRequestHeader',
      ),
      'responseId' => 
      array (
        'setter' => 'setResponseId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'sourceId' => 
      array (
        'setter' => 'setSourceId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'targetId' => 
      array (
        'setter' => 'setTargetId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'TestScript.RequestHeader' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptRequestHeader',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'field' => 
      array (
        'setter' => 'setField',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'TestScript.Assert' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptAssert',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'label' => 
      array (
        'setter' => 'setLabel',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'direction' => 
      array (
        'setter' => 'setDirection',
        'element' => 'AssertionDirectionType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAssertionDirectionType',
      ),
      'compareToSourceId' => 
      array (
        'setter' => 'setCompareToSourceId',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'compareToSourcePath' => 
      array (
        'setter' => 'setCompareToSourcePath',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contentType' => 
      array (
        'setter' => 'setContentType',
        'element' => 'ContentType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContentType',
      ),
      'headerField' => 
      array (
        'setter' => 'setHeaderField',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'minimumId' => 
      array (
        'setter' => 'setMinimumId',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'navigationLinks' => 
      array (
        'setter' => 'setNavigationLinks',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'operator' => 
      array (
        'setter' => 'setOperator',
        'element' => 'AssertionOperatorType',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAssertionOperatorType',
      ),
      'path' => 
      array (
        'setter' => 'setPath',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'resource' => 
      array (
        'setter' => 'setResource',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'response' => 
      array (
        'setter' => 'setResponse',
        'element' => 'AssertionResponseTypes',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRAssertionResponseTypes',
      ),
      'responseCode' => 
      array (
        'setter' => 'setResponseCode',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'sourceId' => 
      array (
        'setter' => 'setSourceId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'validateProfileId' => 
      array (
        'setter' => 'setValidateProfileId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'warningOnly' => 
      array (
        'setter' => 'setWarningOnly',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
    ),
  ),
  'TestScript.Test' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptTest',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'metadata' => 
      array (
        'setter' => 'setMetadata',
        'element' => 'TestScript.Metadata',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptMetadata',
      ),
      'action' => 
      array (
        'setter' => 'addAction',
        'element' => 'TestScript.Action1',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptAction1',
      ),
    ),
  ),
  'TestScript.Action1' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptAction1',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'operation' => 
      array (
        'setter' => 'setOperation',
        'element' => 'TestScript.Operation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptOperation',
      ),
      'assert' => 
      array (
        'setter' => 'setAssert',
        'element' => 'TestScript.Assert',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptAssert',
      ),
    ),
  ),
  'TestScript.Teardown' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptTeardown',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'action' => 
      array (
        'setter' => 'addAction',
        'element' => 'TestScript.Action2',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptAction2',
      ),
    ),
  ),
  'TestScript.Action2' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptAction2',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'operation' => 
      array (
        'setter' => 'setOperation',
        'element' => 'TestScript.Operation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRTestScript\\FHIRTestScriptOperation',
      ),
    ),
  ),
  'AssertionDirectionType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAssertionDirectionType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AssertionDirectionType-list',
        'type' => 'string',
      ),
    ),
  ),
  'AssertionResponseTypes' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAssertionResponseTypes',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AssertionResponseTypes-list',
        'type' => 'string',
      ),
    ),
  ),
  'AssertionOperatorType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRAssertionOperatorType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'AssertionOperatorType-list',
        'type' => 'string',
      ),
    ),
  ),
  'ContentType' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRContentType',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'ContentType-list',
        'type' => 'string',
      ),
    ),
  ),
  'ValueSet' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRValueSet',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'url' => 
      array (
        'setter' => 'setUrl',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'status' => 
      array (
        'setter' => 'setStatus',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'experimental' => 
      array (
        'setter' => 'setExperimental',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'publisher' => 
      array (
        'setter' => 'setPublisher',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contact' => 
      array (
        'setter' => 'addContact',
        'element' => 'ValueSet.Contact',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetContact',
      ),
      'date' => 
      array (
        'setter' => 'setDate',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'lockedDate' => 
      array (
        'setter' => 'setLockedDate',
        'element' => 'date',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDate',
      ),
      'description' => 
      array (
        'setter' => 'setDescription',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'useContext' => 
      array (
        'setter' => 'addUseContext',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'immutable' => 
      array (
        'setter' => 'setImmutable',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'requirements' => 
      array (
        'setter' => 'setRequirements',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'copyright' => 
      array (
        'setter' => 'setCopyright',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'extensible' => 
      array (
        'setter' => 'setExtensible',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'codeSystem' => 
      array (
        'setter' => 'setCodeSystem',
        'element' => 'ValueSet.CodeSystem',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetCodeSystem',
      ),
      'compose' => 
      array (
        'setter' => 'setCompose',
        'element' => 'ValueSet.Compose',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetCompose',
      ),
      'expansion' => 
      array (
        'setter' => 'setExpansion',
        'element' => 'ValueSet.Expansion',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetExpansion',
      ),
    ),
  ),
  'ValueSet.Contact' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetContact',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'telecom' => 
      array (
        'setter' => 'addTelecom',
        'element' => 'ContactPoint',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRContactPoint',
      ),
    ),
  ),
  'ValueSet.CodeSystem' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetCodeSystem',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'caseSensitive' => 
      array (
        'setter' => 'setCaseSensitive',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'concept' => 
      array (
        'setter' => 'addConcept',
        'element' => 'ValueSet.Concept',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetConcept',
      ),
    ),
  ),
  'ValueSet.Concept' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetConcept',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'abstract' => 
      array (
        'setter' => 'setAbstract',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'display' => 
      array (
        'setter' => 'setDisplay',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'definition' => 
      array (
        'setter' => 'setDefinition',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'designation' => 
      array (
        'setter' => 'addDesignation',
        'element' => 'ValueSet.Designation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetDesignation',
      ),
      'concept' => 
      array (
        'setter' => 'addConcept',
        'element' => 'ValueSet.Concept',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetConcept',
      ),
    ),
  ),
  'ValueSet.Designation' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetDesignation',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'use' => 
      array (
        'setter' => 'setUse',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'ValueSet.Compose' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetCompose',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'import' => 
      array (
        'setter' => 'addImport',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'include' => 
      array (
        'setter' => 'addInclude',
        'element' => 'ValueSet.Include',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetInclude',
      ),
      'exclude' => 
      array (
        'setter' => 'addExclude',
        'element' => 'ValueSet.Include',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetInclude',
      ),
    ),
  ),
  'ValueSet.Include' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetInclude',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'concept' => 
      array (
        'setter' => 'addConcept',
        'element' => 'ValueSet.Concept1',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetConcept1',
      ),
      'filter' => 
      array (
        'setter' => 'addFilter',
        'element' => 'ValueSet.Filter',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetFilter',
      ),
    ),
  ),
  'ValueSet.Concept1' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetConcept1',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'display' => 
      array (
        'setter' => 'setDisplay',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'designation' => 
      array (
        'setter' => 'addDesignation',
        'element' => 'ValueSet.Designation',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetDesignation',
      ),
    ),
  ),
  'ValueSet.Filter' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetFilter',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'property' => 
      array (
        'setter' => 'setProperty',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'op' => 
      array (
        'setter' => 'setOp',
        'element' => 'FilterOperator',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRFilterOperator',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'ValueSet.Expansion' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetExpansion',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'setIdentifier',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'timestamp' => 
      array (
        'setter' => 'setTimestamp',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'total' => 
      array (
        'setter' => 'setTotal',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'offset' => 
      array (
        'setter' => 'setOffset',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'parameter' => 
      array (
        'setter' => 'addParameter',
        'element' => 'ValueSet.Parameter',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetParameter',
      ),
      'contains' => 
      array (
        'setter' => 'addContains',
        'element' => 'ValueSet.Contains',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetContains',
      ),
    ),
  ),
  'ValueSet.Parameter' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetParameter',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'name' => 
      array (
        'setter' => 'setName',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valueString' => 
      array (
        'setter' => 'setValueString',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'valueBoolean' => 
      array (
        'setter' => 'setValueBoolean',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'valueInteger' => 
      array (
        'setter' => 'setValueInteger',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'valueDecimal' => 
      array (
        'setter' => 'setValueDecimal',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'valueUri' => 
      array (
        'setter' => 'setValueUri',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'valueCode' => 
      array (
        'setter' => 'setValueCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
    ),
  ),
  'ValueSet.Contains' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetContains',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'system' => 
      array (
        'setter' => 'setSystem',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'abstract' => 
      array (
        'setter' => 'setAbstract',
        'element' => 'boolean',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRBoolean',
      ),
      'version' => 
      array (
        'setter' => 'setVersion',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'code' => 
      array (
        'setter' => 'setCode',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'display' => 
      array (
        'setter' => 'setDisplay',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'contains' => 
      array (
        'setter' => 'addContains',
        'element' => 'ValueSet.Contains',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRValueSet\\FHIRValueSetContains',
      ),
    ),
  ),
  'FilterOperator' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRFilterOperator',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'FilterOperator-list',
        'type' => 'string',
      ),
    ),
  ),
  'VisionPrescription' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRDomainResource\\FHIRVisionPrescription',
    'extendedElementName' => 'DomainResource',
    'properties' => 
    array (
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRId',
      ),
      'meta' => 
      array (
        'setter' => 'setMeta',
        'element' => 'Meta',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRMeta',
      ),
      'implicitRules' => 
      array (
        'setter' => 'setImplicitRules',
        'element' => 'uri',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRUri',
      ),
      'language' => 
      array (
        'setter' => 'setLanguage',
        'element' => 'code',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCode',
      ),
      'text' => 
      array (
        'setter' => 'setText',
        'element' => 'Narrative',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRNarrative',
      ),
      'contained' => 
      array (
        'setter' => 'addContained',
        'element' => 'ResourceContainer',
        'type' => 'FHIR_DSTU_TWO\\FHIRResourceContainer',
      ),
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'identifier' => 
      array (
        'setter' => 'addIdentifier',
        'element' => 'Identifier',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRIdentifier',
      ),
      'dateWritten' => 
      array (
        'setter' => 'setDateWritten',
        'element' => 'dateTime',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDateTime',
      ),
      'patient' => 
      array (
        'setter' => 'setPatient',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'prescriber' => 
      array (
        'setter' => 'setPrescriber',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'encounter' => 
      array (
        'setter' => 'setEncounter',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'reasonCodeableConcept' => 
      array (
        'setter' => 'setReasonCodeableConcept',
        'element' => 'CodeableConcept',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCodeableConcept',
      ),
      'reasonReference' => 
      array (
        'setter' => 'setReasonReference',
        'element' => 'Reference',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRReference',
      ),
      'dispense' => 
      array (
        'setter' => 'addDispense',
        'element' => 'VisionPrescription.Dispense',
        'type' => 'FHIR_DSTU_TWO\\FHIRResource\\FHIRVisionPrescription\\FHIRVisionPrescriptionDispense',
      ),
    ),
  ),
  'VisionPrescription.Dispense' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRResource\\FHIRVisionPrescription\\FHIRVisionPrescriptionDispense',
    'extendedElementName' => 'BackboneElement',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'modifierExtension' => 
      array (
        'setter' => 'addModifierExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'product' => 
      array (
        'setter' => 'setProduct',
        'element' => 'Coding',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRCoding',
      ),
      'eye' => 
      array (
        'setter' => 'setEye',
        'element' => 'VisionEyes',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRVisionEyes',
      ),
      'sphere' => 
      array (
        'setter' => 'setSphere',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'cylinder' => 
      array (
        'setter' => 'setCylinder',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'axis' => 
      array (
        'setter' => 'setAxis',
        'element' => 'integer',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRInteger',
      ),
      'prism' => 
      array (
        'setter' => 'setPrism',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'base' => 
      array (
        'setter' => 'setBase',
        'element' => 'VisionBase',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRVisionBase',
      ),
      'add' => 
      array (
        'setter' => 'setAdd',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'power' => 
      array (
        'setter' => 'setPower',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'backCurve' => 
      array (
        'setter' => 'setBackCurve',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'diameter' => 
      array (
        'setter' => 'setDiameter',
        'element' => 'decimal',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRDecimal',
      ),
      'duration' => 
      array (
        'setter' => 'setDuration',
        'element' => 'SimpleQuantity',
        'type' => 'FHIR_DSTU_TWO\\FHIRSimpleQuantity',
      ),
      'color' => 
      array (
        'setter' => 'setColor',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'brand' => 
      array (
        'setter' => 'setBrand',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
      'notes' => 
      array (
        'setter' => 'setNotes',
        'element' => 'string',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRString',
      ),
    ),
  ),
  'VisionBase' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRVisionBase',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'VisionBase-list',
        'type' => 'string',
      ),
    ),
  ),
  'VisionEyes' => 
  array (
    'fullClassName' => '\\FHIR_DSTU_TWO\\FHIRElement\\FHIRVisionEyes',
    'extendedElementName' => 'Element',
    'properties' => 
    array (
      'extension' => 
      array (
        'setter' => 'addExtension',
        'element' => 'Extension',
        'type' => 'FHIR_DSTU_TWO\\FHIRElement\\FHIRExtension',
      ),
      'id' => 
      array (
        'setter' => 'setId',
        'element' => 'id-primitive',
        'type' => 'string',
      ),
      'value' => 
      array (
        'setter' => 'setValue',
        'element' => 'VisionEyes-list',
        'type' => 'string',
      ),
    ),
  ),
);

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_bigDumbMap[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (isset($this->_bigDumbMap[$offset]))
            return $this->_bigDumbMap[$offset];

        trigger_error(sprintf(
            'Offset %s does not exist in the FHIR element map, this could either mean a malformed response or a bug in the generator.',
            $offset
        ));

        return null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Not allowed to set values on the FHIR parser element map');
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Not allowed to unset values in this FHIR parser element map');
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->_bigDumbMap);
    }

    /**
     * @return string
     */
    public function key()
    {
        return key($this->_bigDumbMap);
    }

    public function next()
    {
        next($this->_bigDumbMap);
    }

    public function rewind()
    {
        reset($this->_bigDumbMap);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return key($this->_bigDumbMap) !== null;
    }
}