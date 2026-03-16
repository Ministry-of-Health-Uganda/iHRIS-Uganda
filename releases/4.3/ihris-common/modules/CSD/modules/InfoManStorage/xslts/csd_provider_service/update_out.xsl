<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn='urn:openhie.org:openinfoman-hwr:stored-function:health_worker_update_service'>
	<requestParams>                     
	  <id><xsl:attribute name='entityID'><xsl:value-of select="substring-before(/form/@id,'/')"/></xsl:attribute></id>                     
	  <facility>
	    <xsl:attribute name='entityID'><xsl:value-of select="substring-before(substring-after(/form/@id,'/'),'/')"/></xsl:attribute>
	    <service>
	      <xsl:if test="/form/field[@name='csd_service']/value">
		<xsl:attribute name='entityID'><xsl:value-of select="/form/field[@name='csd_service']/value"/></xsl:attribute>
	      </xsl:if>

	      <xsl:attribute name='position'><xsl:value-of select="substring-after(substring-after(/form/@id,'/'),'/')"/></xsl:attribute>
	      <xsl:if test="/form/field[@name='freeBusyURI']">
		<freeBusyURI><xsl:value-of select="/form/field[@name='freeBusyURI']"/></freeBusyURI>
	      </xsl:if>
	      <xsl:if test="/form/field[@name='csd_organization']/value">
		<organization><xsl:attribute name='entityID'><xsl:value-of select="/form/field[@name='csd_organization']/value"/></xsl:attribute></organization>
	      </xsl:if>
	      <xsl:for-each select="/form/field[@name='csd_language']/value">
		<language>
		  <xsl:attribute name='codingScheme'><xsl:value-of select="substring-before(text(),'@@@')"/></xsl:attribute>
		  <xsl:attribute name='code'><xsl:value-of select="substring-after(text(),'@@@')"/></xsl:attribute>
		</language>
	      </xsl:for-each>

	    </service>                                        
	  </facility>
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     
  </xsl:template> 
</xsl:stylesheet>
