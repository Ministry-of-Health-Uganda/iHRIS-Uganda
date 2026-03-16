<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <xsl:for-each select="/csd:CSD/csd:organizationDirectory/csd:organization/csd:otherID">      
      <form name='csd_organization_otherid' parent_form='csd_organization'>
	<xsl:attribute name="id"><xsl:value-of select="concat(../@entityID,'/',@position)"/></xsl:attribute>
	<xsl:attribute name="parent_id"><xsl:value-of select="../@entityID"/></xsl:attribute>
	<xsl:attribute name="created"><xsl:value-of select="translate(substring(../csd:record/@created,1,19),'T',' ')"/></xsl:attribute>
	<xsl:attribute name="modified"><xsl:value-of select="translate(substring(../csd:record/@updated,1,19),'T',' ')"/></xsl:attribute>
	<xsl:if test="@assigningAuthorityName">        
	  <field name="assigning_authority" type="STRING_LINE"><xsl:value-of select="@assigningAuthorityName"/></field> 
	</xsl:if>
	<xsl:if test="@code">        
	  <field name="csd_organization_otherid_type" type="STRING_LINE"><xsl:value-of select="@code"/></field> 
	</xsl:if>
	<xsl:if test="text()">        
	  <field name="number" type="STRING_LINE"><xsl:value-of select="text()"/></field> 
	</xsl:if>
      </form>
    </xsl:for-each>
  </xsl:template> 
</xsl:stylesheet>
