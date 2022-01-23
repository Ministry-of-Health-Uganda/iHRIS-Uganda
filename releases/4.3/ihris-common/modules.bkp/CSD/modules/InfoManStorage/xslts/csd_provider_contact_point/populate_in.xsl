<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <xsl:for-each select="/csd:CSD/csd:providerDirectory/csd:provider/csd:demographic/csd:contactPoint[1]">      
      <form name='csd_provider_contact_point' parent_form='csd_provider'>
	<xsl:attribute name="id"><xsl:value-of select="concat(../../@entityID,'/',@position)"/></xsl:attribute>
	<xsl:attribute name="parent_id"><xsl:value-of select="../../@entityID"/></xsl:attribute>
	<xsl:attribute name="created"><xsl:value-of select="translate(substring(../../csd:record/@created,1,19),'T',' ')"/></xsl:attribute>
	<xsl:attribute name="modified"><xsl:value-of select="translate(substring(../../csd:record/@updated,1,19),'T',' ')"/></xsl:attribute>
	<field name="value" type="STRING_LINE"><xsl:value-of select="./csd:codedType/text()"/></field>
	<xsl:if test="./csd:equipment">        
	  <field name="equipment" type="STRING_LINE"><xsl:value-of select="./csd:equipment"/></field> 
	</xsl:if>
	<xsl:if test="./csd:purpose">        
	  <field name="purpose" type="STRING_LINE"><xsl:value-of select="./csd:purpose"/></field> 
	</xsl:if>
	<xsl:if test="./csd:certificate">        
	  <field name="certificate" type="STRING_LINE"><xsl:value-of select="./csd:certificate"/></field> 
	</xsl:if>
	<xsl:if test="./csd:codedType">        
	  <field name="csd_provider_contact_point_type" type="MAP"><value form='csd_provider_contact_point_type'><xsl:value-of select="concat(csd:codedType/@codingScheme,'@@@',csd:codedType/@code)"/></value></field> 
	</xsl:if>
      </form>
    </xsl:for-each>
  </xsl:template> 
</xsl:stylesheet>
