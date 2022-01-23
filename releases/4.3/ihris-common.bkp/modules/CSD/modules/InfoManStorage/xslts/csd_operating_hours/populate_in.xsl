<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <xsl:for-each select="/csd:CSD/csd:providerDirectory/csd:provider/csd:facilities/csd:facility/csd:service/csd:operatingHours[1]">      
      <form name='csd_operating_hours' parent_form='csd_provider_service'>
	<xsl:attribute name="id"><xsl:value-of select="concat(../../../../@entityID,'/',../../@entityID, '/', ../@position,'/',@position)"/></xsl:attribute>
	<xsl:attribute name="parent_id"><xsl:value-of select="concat(../../../../@entityID,'/',../../@entityID,'/',../@position)"/></xsl:attribute>
	<xsl:attribute name="created"><xsl:value-of select="translate(substring(../../../csd:record/@created,1,19),'T',' ')"/></xsl:attribute>
	<xsl:attribute name="modified"><xsl:value-of select="translate(substring(../../../csd:record/@updated,1,19),'T',' ')"/></xsl:attribute>
	<xsl:if test="csd:openFlag">
	  <field name='open' type='BOOL'><xsl:value-of select="csd:openFlag/text()"/></field>
	</xsl:if>
	<xsl:if test="csd:dayOfTheWeek">
	  <field name='day_of_week' type='MAP'><value form='day_of_week'><xsl:value-of select="csd:dayOfTheWeek/text()"/></value></field>
	</xsl:if>
	<xsl:if test="csd:beginningHour">
	  <field name='begin_time' type='DATE_HMS'>
	    <hour><xsl:value-of select="substring-before(csd:beginningHour,'/')"/></hour>
	    <minute><xsl:value-of select="substring-before(substring-after(csd:beginningHour,'/'),'/')"/></minute>
	    <second><xsl:value-of select="substring-after(substring-after(csd:beginningHour,'/'),'/')"/></second>
	  </field>
	</xsl:if>
	<xsl:if test="csd:endingHour">
	  <field name='begin_time' type='DATE_HMS'>
	    <hour><xsl:value-of select="substring-before(csd:endingHour,'/')"/></hour>
	    <minute><xsl:value-of select="substring-before(substring-after(csd:endingHour,'/'),'/')"/></minute>
	    <second><xsl:value-of select="substring-after(substring-after(csd:endingHour,'/'),'/')"/></second>
	  </field>
	</xsl:if>
	<xsl:if test="csd:beginEffectiveDate">
	  <field name='begin_date' type='DATE_YMD'>
	    <year><xsl:value-of select="substring-before(csd:beginEffectiveDate,'-')"/></year>
	    <month><xsl:value-of select="substring-before(substring-after(csd:beginEffectiveDate,'-'),'-')"/></month>
	    <day><xsl:value-of select="substring-after(substring-after(csd:beginEffectiveDate,'-'),'-')"/></day>
	  </field>
	</xsl:if>
	<xsl:if test="csd:endEffectiveDate">
	  <field name='end_date' type='DATE_YMD'>
	    <year><xsl:value-of select="substring-before(csd:endEffectiveDate,'-')"/></year>
	    <month><xsl:value-of select="substring-before(substring-after(csd:endEffectiveDate,'-'),'-')"/></month>
	    <day><xsl:value-of select="substring-after(substring-after(csd:endEffectiveDate,'-'),'-')"/></day>
	  </field>
	</xsl:if>
	
	
      </form>
    </xsl:for-each>
  </xsl:template> 
</xsl:stylesheet>
