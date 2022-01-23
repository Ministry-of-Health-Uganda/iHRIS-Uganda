<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn="urn:openhie.org:openinfoman-hwr:stored-function:health_worker_update_provider">
	<requestParams>                     
	  <id><xsl:attribute name='entityID'><xsl:value-of select="/form/@id"/></xsl:attribute></id>                     
	  <xsl:for-each select="/form/field[@name='csd_provider_type']/value">
	    <codedType>
	      <xsl:attribute name='codingScheme'><xsl:value-of select="substring-before(text(),'@@@')"/></xsl:attribute>
	      <xsl:attribute name='code'><xsl:value-of select="substring-after(text(),'@@@')"/></xsl:attribute>
	    </codedType>
	  </xsl:for-each>
	  <xsl:if test="/form/field[@name='csd_provider_gender']/value"><gender><xsl:value-of select="/form/field[@name='csd_provider_gender']/value"/></gender></xsl:if>
	  <xsl:if test="/form/field[@name='date_of_birth']">
	    <dateOfBirth>
	      <xsl:value-of select="/form/field[@name='date_of_birth']/year"/>-<xsl:value-of select="/form/field[@name='date_of_birth']/month"/>-<xsl:value-of select="/form/field[@name='date_of_birth']/day"/>
	    </dateOfBirth>
	  </xsl:if>
	  <xsl:for-each select="/form/field[@name='csd_language']/value">
	    <language>
	      <xsl:attribute name='codingScheme'><xsl:value-of select="substring-before(text(),'@@@')"/></xsl:attribute>
	      <xsl:attribute name='code'><xsl:value-of select="substring-after(text(),'@@@')"/></xsl:attribute>
	    </language>
	  </xsl:for-each>
	  <xsl:for-each select="/form/field[@name='csd_provider_specialty']/value">
	    <specialty>
	      <xsl:attribute name='codingScheme'><xsl:value-of select="substring-before(text(),'@@@')"/></xsl:attribute>
	      <xsl:attribute name='code'><xsl:value-of select="substring-after(text(),'@@@')"/></xsl:attribute>
	    </specialty>
	  </xsl:for-each>
	  <xsl:if test="/form/field[@name='csd_provider_status']/value">
	    <status><xsl:value-of select="/form/field[@name='csd_provider_status']/value"/></status>
	  </xsl:if>
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     
  </xsl:template> 
</xsl:stylesheet>
