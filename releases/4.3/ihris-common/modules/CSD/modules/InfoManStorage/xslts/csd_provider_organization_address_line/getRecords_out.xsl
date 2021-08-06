<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn="ba3bde36-943b-4cec-8eb2-331063a54403">
	<requestParams>                     
	  <xsl:choose>
	    <xsl:when test="/form/@parent_form = 'csd_provider_name' and /form/@parent_id">
	      <id><xsl:attribute name='entityID'><xsl:value-of select="substring-before(/form/@parent_id,'/')"/></xsl:attribute></id>
	      <name><xsl:attribute name='position'><xsl:value-of select="substring-after(/form/@parent_id,'/')"/></xsl:attribute></name>
	    </xsl:when>
	    <xsl:otherwise>
	      <id/>
	    </xsl:otherwise>
	  </xsl:choose>
	  <record>
	  	<xsl:if test="/form/@modified">
	  		<xsl:attribute name='updated'><xsl:value-of select="translate(substring(/form/@modified,1,19),' ','T')"/></xsl:attribute>
	  	</xsl:if>
	  </record>                     
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     
  </xsl:template> 
</xsl:stylesheet>
