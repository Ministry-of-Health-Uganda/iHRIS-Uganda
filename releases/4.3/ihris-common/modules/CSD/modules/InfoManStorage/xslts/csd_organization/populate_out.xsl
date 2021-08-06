<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn="urn:ihe:iti:csd:2014:stored-function:organization-search">                 
	<requestParams>                     
	  <id><xsl:attribute name='entityID'><xsl:value-of select="/form/@id"/></xsl:attribute></id>                     
	  <otherID/>
	  <primaryName/>                     
	  <name/>                     
	  <codedType/>                     
	  <address/>                     
	  <start/>                     
	  <max/>
	  <record/>                                      
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     
  </xsl:template> 
</xsl:stylesheet>
