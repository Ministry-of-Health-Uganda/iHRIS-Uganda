<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn="urn:openhie.org:openinfoman-hwr:stored-function:health_worker_create_provider_facility">
	<requestParams>                     
	  <id><xsl:attribute name='entityID'><xsl:value-of select="/form/@parent_id"/></xsl:attribute></id>                     
	  <facility><xsl:attribute name='entityID'><xsl:value-of select="/form/field[@name='csd_facility']"/></xsl:attribute></facility>                                        
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     
  </xsl:template> 
</xsl:stylesheet>
